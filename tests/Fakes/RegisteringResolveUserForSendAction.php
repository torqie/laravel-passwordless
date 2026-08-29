<?php

namespace Torqie\LaravelPasswordless\Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Actions\ResolveUserForSendAction;
use Torqie\LaravelPasswordless\Tests\Models\User;

/**
 * The passwordless sign-up use case: an unknown email gets an account instead
 * of being silently dropped. Rate limiting stays with the parent action.
 */
class RegisteringResolveUserForSendAction extends ResolveUserForSendAction
{
    public static int $created = 0;

    public static function reset(): void
    {
        self::$created = 0;
    }

    public function handle(string $email, string $ip): ?Authenticatable
    {
        $user = parent::handle($email, $ip);

        if ($user !== null) {
            return $user;
        }

        self::$created++;

        return User::create(['email' => $email]);
    }
}
