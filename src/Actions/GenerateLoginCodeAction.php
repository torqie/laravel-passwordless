<?php

namespace Torqie\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Contracts\GeneratesLoginCode;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Support\TokenGenerator;

class GenerateLoginCodeAction implements GeneratesLoginCode
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator,
    ) {}

    public function generate(Authenticatable $authenticatable): string
    {
        $charset = (string) config('passwordless.code.charset', '0123456789');
        $length = (int) config('passwordless.code.length', 6);
        $ttl = (int) config('passwordless.ttl', 15);
        $plainCode = $this->tokenGenerator->makeCode($charset, $length);
        $salt = $this->tokenGenerator->makeSalt();

        $owned = fn () => PasswordlessToken::query()
            ->where('authenticatable_type', $authenticatable->getMorphClass())
            ->where('authenticatable_id', $authenticatable->getAuthIdentifier())
            ->ofType('login_code');

        // Revoke any existing valid tokens so only one is active at a time.
        $owned()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);

        // Drop this user's spent codes, including the ones just revoked. Without
        // this they accumulate until `passwordless:purge` runs, and every retained
        // row is another chance for a new code to collide on the unique index —
        // a 6-digit numeric code only has 1,000,000 possible digests.
        $owned()
            ->where(function ($query): void {
                $query->whereNotNull('used_at')
                    ->orWhere('expires_at', '<=', now());
            })
            ->delete();

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id' => $authenticatable->getAuthIdentifier(),
            'token' => $this->tokenGenerator->hash($plainCode, $salt),
            'salt' => $salt,
            'type' => 'login_code',
            'expires_at' => now()->addMinutes($ttl),
        ]);

        return $plainCode;
    }
}
