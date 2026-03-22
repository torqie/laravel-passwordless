<?php

namespace Torqie\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Torqie\LaravelPasswordless\Contracts\AuthenticatesViaMagicLink;
use Torqie\LaravelPasswordless\Events\UserAuthenticatedPasswordlessly;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;

class AuthenticateViaMagicLinkAction implements AuthenticatesViaMagicLink
{
    public function authenticate(string $token): RedirectResponse
    {
        $hashedToken = hash('sha256', $token);
        $invalidRedirect = (string) config('passwordless.redirects.invalid_token', '/login');

        /** @var PasswordlessToken|null $passwordlessToken */
        $passwordlessToken = PasswordlessToken::ofType('magic_link')
            ->valid()
            ->where('token', $hashedToken)
            ->first();

        if ($passwordlessToken === null) {
            return redirect()->to($invalidRedirect)
                ->withErrors(['token' => 'This magic link is invalid or has expired.']);
        }

        $authenticatable = $passwordlessToken->authenticatable;

        if (! $authenticatable instanceof Authenticatable) {
            return redirect()->to($invalidRedirect)
                ->withErrors(['token' => 'This magic link is invalid or has expired.']);
        }

        $passwordlessToken->markUsed();

        $remember = (bool) config('passwordless.remember', false);
        Auth::guard((string) config('passwordless.guard', 'web'))->login($authenticatable, $remember);

        // Prevent session-fixation attacks by rotating the session ID.
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        event(new UserAuthenticatedPasswordlessly($authenticatable, 'magic_link'));

        return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
    }
}
