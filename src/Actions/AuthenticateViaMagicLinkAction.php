<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Wiredrhino\LaravelPasswordless\Contracts\AuthenticatesViaMagicLink;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

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

        Auth::guard((string) config('passwordless.guard', 'web'))->login($authenticatable);

        return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
    }
}



