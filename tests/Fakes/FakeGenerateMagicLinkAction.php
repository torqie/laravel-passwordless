<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Contracts\GeneratesMagicLink;

class FakeGenerateMagicLinkAction implements GeneratesMagicLink
{
    public const URL = 'https://example.test/fake-magic-link';

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

        return self::URL;
    }
}
