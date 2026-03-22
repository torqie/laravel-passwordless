<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesLoginCode;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;
use Wiredrhino\LaravelPasswordless\Support\TokenGenerator;

class GenerateLoginCodeAction implements GeneratesLoginCode
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator,
    ) {}

    public function generate(Authenticatable $authenticatable): string
    {
        $charset   = (string) config('passwordless.code.charset', '0123456789');
        $length    = (int) config('passwordless.code.length', 6);
        $ttl       = (int) config('passwordless.ttl', 15);
        $plainCode = $this->tokenGenerator->makeCode($charset, $length);

        // Revoke any existing valid tokens so only one is active at a time.
        PasswordlessToken::where('authenticatable_type', $authenticatable->getMorphClass())
            ->where('authenticatable_id', $authenticatable->getAuthIdentifier())
            ->ofType('login_code')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id'   => $authenticatable->getAuthIdentifier(),
            'token'                => $this->tokenGenerator->hash($plainCode),
            'type'                 => 'login_code',
            'expires_at'           => now()->addMinutes($ttl),
        ]);

        return $plainCode;
    }
}
