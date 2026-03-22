<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesMagicLink;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

class GenerateMagicLinkAction implements GeneratesMagicLink
{
    public function generate(Authenticatable $authenticatable): string
    {
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);
        $ttl = (int) config('passwordless.ttl', 15);

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id'   => $authenticatable->getAuthIdentifier(),
            'token'                => $hashedToken,
            'type'                 => 'magic_link',
            'plain_text'           => null,
            'expires_at'           => now()->addMinutes($ttl),
        ]);

        return URL::temporarySignedRoute(
            'passwordless.magic-link.authenticate',
            now()->addMinutes($ttl),
            ['token' => $plainToken]
        );
    }
}

