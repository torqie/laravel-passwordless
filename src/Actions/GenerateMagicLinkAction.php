<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesMagicLink;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;
use Wiredrhino\LaravelPasswordless\Support\SignedUrlBuilder;
use Wiredrhino\LaravelPasswordless\Support\TokenGenerator;

class GenerateMagicLinkAction implements GeneratesMagicLink
{
    public function __construct(
        private readonly TokenGenerator  $tokenGenerator,
        private readonly SignedUrlBuilder $urlBuilder,
    ) {}

    public function generate(Authenticatable $authenticatable): string
    {
        $ttl   = (int) config('passwordless.ttl', 15);
        $token = $this->tokenGenerator->makeToken();

        // Revoke any existing valid tokens so only one is active at a time.
        PasswordlessToken::where('authenticatable_type', $authenticatable->getMorphClass())
            ->where('authenticatable_id', $authenticatable->getAuthIdentifier())
            ->ofType('magic_link')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id'   => $authenticatable->getAuthIdentifier(),
            'token'                => $token['hashed'],
            'type'                 => 'magic_link',
            'expires_at'           => now()->addMinutes($ttl),
        ]);

        return $this->urlBuilder->buildMagicLinkUrl($token['plain'], $ttl);
    }
}
