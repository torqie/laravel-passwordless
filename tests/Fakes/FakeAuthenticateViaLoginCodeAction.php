<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Http\RedirectResponse;
use Torqie\LaravelPasswordless\Contracts\AuthenticatesViaLoginCode;

class FakeAuthenticateViaLoginCodeAction implements AuthenticatesViaLoginCode
{
    public const REDIRECT = '/fake-login-code-authenticated';

    public static bool $called = false;

    public static ?string $receivedEmail = null;

    public static ?string $receivedCode = null;

    public static function reset(): void
    {
        self::$called = false;
        self::$receivedEmail = null;
        self::$receivedCode = null;
    }

    public function authenticate(string $email, string $code): RedirectResponse
    {
        self::$called = true;
        self::$receivedEmail = $email;
        self::$receivedCode = $code;

        return redirect()->to(self::REDIRECT);
    }
}
