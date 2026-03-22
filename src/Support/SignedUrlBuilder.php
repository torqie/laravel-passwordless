<?php

namespace Wiredrhino\LaravelPasswordless\Support;

use Illuminate\Support\Facades\URL;

class SignedUrlBuilder
{
    /**
     * Build a temporary signed route URL.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function build(string $routeName, int $ttlMinutes, array $parameters = []): string
    {
        return URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes($ttlMinutes),
            $parameters
        );
    }

    /**
     * Build the magic link authenticate URL for a plain-text token.
     */
    public function buildMagicLinkUrl(string $plainToken, int $ttlMinutes): string
    {
        return $this->build(
            'passwordless.magic-link.authenticate',
            $ttlMinutes,
            ['token' => $plainToken]
        );
    }
}
