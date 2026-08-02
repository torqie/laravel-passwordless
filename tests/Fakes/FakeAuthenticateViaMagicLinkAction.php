<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Http\RedirectResponse;
use Torqie\LaravelPasswordless\Contracts\AuthenticatesViaMagicLink;

class FakeAuthenticateViaMagicLinkAction implements AuthenticatesViaMagicLink
{
    public const REDIRECT = '/fake-magic-link-authenticated';

    public static bool $called = false;

    public static ?string $receivedToken = null;

    public static function reset(): void
    {
        self::$called = false;
        self::$receivedToken = null;
    }

    public function authenticate(string $token): RedirectResponse
    {
        self::$called = true;
        self::$receivedToken = $token;

        return redirect()->to(self::REDIRECT);
    }
}
