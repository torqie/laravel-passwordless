<?php

namespace Torqie\LaravelPasswordless\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

interface ResolvesUserForSend
{
    /**
     * Throttle the send endpoint and resolve the authenticatable for $email.
     *
     * Return null when no authenticatable should receive a token — the caller
     * handles this silently to prevent user-enumeration. Implementations are
     * free to create an account instead of returning null (passwordless
     * sign-up); the caller treats whatever is returned as the recipient.
     *
     * @throws ValidationException When the send rate limit has been exceeded.
     */
    public function handle(string $email, string $ip): ?Authenticatable;
}
