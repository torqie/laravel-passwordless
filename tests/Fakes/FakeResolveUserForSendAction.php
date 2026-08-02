<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Contracts\ResolvesUserForSend;

class FakeResolveUserForSendAction implements ResolvesUserForSend
{
    public static bool $called = false;

    public static ?string $receivedEmail = null;

    public static ?string $receivedIp = null;

    /**
     * The authenticatable this fake hands back, whatever the email says.
     */
    public static ?Authenticatable $returns = null;

    public static function reset(): void
    {
        self::$called = false;
        self::$receivedEmail = null;
        self::$receivedIp = null;
        self::$returns = null;
    }

    public function handle(string $email, string $ip): ?Authenticatable
    {
        self::$called = true;
        self::$receivedEmail = $email;
        self::$receivedIp = $ip;

        return self::$returns;
    }
}
