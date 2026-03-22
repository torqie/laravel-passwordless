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

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id'   => $authenticatable->getAuthIdentifier(),
            'token'                => $this->tokenGenerator->hash($plainCode),
            'type'                 => 'login_code',
            'plain_text'           => null,
            'expires_at'           => now()->addMinutes($ttl),
        ]);

        return $plainCode;
    }
}
