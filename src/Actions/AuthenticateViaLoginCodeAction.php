<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Wiredrhino\LaravelPasswordless\Contracts\AuthenticatesViaLoginCode;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

class AuthenticateViaLoginCodeAction implements AuthenticatesViaLoginCode
{
    private const MAX_ATTEMPTS = 5;

    /**
     * @throws ValidationException
     */
    public function authenticate(string $email, string $code): RedirectResponse
    {
        $rateLimitKey = 'passwordless:code:' . Str::lower($email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw ValidationException::withMessages([
                'code' => [trans('Too many failed attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])],
            ]);
        }

        /** @var class-string $userModel */
        $userModel = config('passwordless.user_model');
        $user      = $userModel::where('email', $email)->first();

        if ($user === null) {
            RateLimiter::hit($rateLimitKey);

            throw ValidationException::withMessages([
                'code' => ['The code is incorrect or has expired.'],
            ]);
        }

        $hashedCode = hash('sha256', $code);

        /** @var PasswordlessToken|null $token */
        $token = PasswordlessToken::ofType('login_code')
            ->valid()
            ->where('authenticatable_type', $user->getMorphClass())
            ->where('authenticatable_id', $user->getAuthIdentifier())
            ->where('token', $hashedCode)
            ->first();

        if ($token === null) {
            RateLimiter::hit($rateLimitKey);

            throw ValidationException::withMessages([
                'code' => ['The code is incorrect or has expired.'],
            ]);
        }

        RateLimiter::clear($rateLimitKey);

        $token->markUsed();

        Auth::guard((string) config('passwordless.guard', 'web'))->login($user);

        return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
    }
}

