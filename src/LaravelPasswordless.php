<?php

namespace Torqie\LaravelPasswordless;

use Illuminate\Contracts\Auth\Authenticatable;
use Torqie\LaravelPasswordless\Contracts\GeneratesLoginCode;
use Torqie\LaravelPasswordless\Contracts\GeneratesMagicLink;
use Torqie\LaravelPasswordless\Events\LoginCodeSent;
use Torqie\LaravelPasswordless\Events\MagicLinkSent;
use Torqie\LaravelPasswordless\Notifications\LoginCodeNotification;
use Torqie\LaravelPasswordless\Notifications\MagicLinkNotification;

class LaravelPasswordless
{
    private ?Authenticatable $user = null;

    public function __construct(
        private readonly GeneratesMagicLink $magicLinkGenerator,
        private readonly GeneratesLoginCode $loginCodeGenerator,
    ) {}

    /**
     * Set the authenticatable model to act on behalf of.
     */
    public function for(Authenticatable $user): static
    {
        $clone       = clone $this;
        $clone->user = $user;

        return $clone;
    }

    /**
     * Generate a magic link, notify the user, and return the signed URL.
     *
     * @throws \LogicException  If called without first calling for().
     */
    public function sendMagicLink(): string
    {
        $user = $this->resolveUser();

        $url = $this->magicLinkGenerator->generate($user);

        // @phpstan-ignore method.notFound ($user is expected to use the Notifiable trait)
        $user->notify(new MagicLinkNotification($url));

        event(new MagicLinkSent($user, $url));

        return $url;
    }

    /**
     * Generate a one-time login code, notify the user, and return the plain code.
     *
     * @throws \LogicException  If called without first calling for().
     */
    public function sendLoginCode(): string
    {
        $user = $this->resolveUser();

        $code = $this->loginCodeGenerator->generate($user);

        // @phpstan-ignore method.notFound ($user is expected to use the Notifiable trait)
        $user->notify(new LoginCodeNotification($code));

        event(new LoginCodeSent($user));

        return $code;
    }

    private function resolveUser(): Authenticatable
    {
        if ($this->user === null) {
            throw new \LogicException('Call LaravelPasswordless::for($user) before sending.');
        }

        return $this->user;
    }
}
