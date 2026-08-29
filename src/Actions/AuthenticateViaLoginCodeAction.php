<?php

namespace Torqie\LaravelPasswordless\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Torqie\LaravelPasswordless\Contracts\AuthenticatesViaLoginCode;
use Torqie\LaravelPasswordless\Events\UserAuthenticatedPasswordlessly;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Support\TokenGenerator;

class AuthenticateViaLoginCodeAction implements AuthenticatesViaLoginCode
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator = new TokenGenerator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function authenticate(string $email, string $code): RedirectResponse
    {
        $maxAttempts = (int) config('passwordless.rate_limits.verify', 5);
        $decaySeconds = (int) config('passwordless.ttl', 15) * 60;
        $rateLimitKey = 'passwordless:code:'.Str::lower($email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw ValidationException::withMessages([
                'code' => [trans('Too many failed attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])],
            ]);
        }

        /** @var class-string $userModel */
        $userModel = config('passwordless.user_model');
        $user = $userModel::where('email', $email)->first();

        if ($user === null) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            throw ValidationException::withMessages([
                'code' => ['The code is incorrect or has expired.'],
            ]);
        }

        // Fetch all valid login-code tokens for this user, then compare hashes in
        // constant time to prevent timing side-channels. Each row is hashed with
        // its own salt; rows written before the salt column existed carry a null
        // salt and still compare against the original unsalted digest, so
        // upgrading does not invalidate codes already in flight.
        /** @var PasswordlessToken|null $token */
        $token = PasswordlessToken::ofType('login_code')
            ->valid()
            ->where('authenticatable_type', $user->getMorphClass())
            ->where('authenticatable_id', $user->getAuthIdentifier())
            ->get()
            ->first(fn (PasswordlessToken $t) => hash_equals(
                $t->token,
                $this->tokenGenerator->hash($code, $t->salt)
            ));

        if ($token === null) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            throw ValidationException::withMessages([
                'code' => ['The code is incorrect or has expired.'],
            ]);
        }

        RateLimiter::clear($rateLimitKey);

        $token->consume();

        $remember = (bool) config('passwordless.remember', false);
        Auth::guard((string) config('passwordless.guard', 'web'))->login($user, $remember);

        // Prevent session-fixation attacks by rotating the session ID.
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        event(new UserAuthenticatedPasswordlessly($user, 'login_code'));

        return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
    }
}
