<?php

namespace Torqie\LaravelPasswordless\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class LoginCodeSent
{
    public function __construct(
        public readonly Authenticatable $authenticatable,
        // The code is intentionally excluded to avoid leaking it via event listeners.
    ) {}
}
