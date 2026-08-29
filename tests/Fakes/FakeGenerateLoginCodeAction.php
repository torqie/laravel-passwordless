<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Contracts\GeneratesLoginCode;

class FakeGenerateLoginCodeAction implements GeneratesLoginCode
{
    public const CODE = 'FAKE-CODE';

    public static bool $called = false;

    public static ?Authenticatable $receivedUser = null;

    public static function reset(): void
    {
        self::$called = false;
        self::$receivedUser = null;
    }

    public function generate(Authenticatable $authenticatable): string
    {
        self::$called = true;
        self::$receivedUser = $authenticatable;

        return self::CODE;
    }
}
