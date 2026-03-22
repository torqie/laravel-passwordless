<?php

namespace Wiredrhino\LaravelPasswordless\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class MagicLinkSent
{
    public function __construct(
        public readonly Authenticatable $authenticatable,
        public readonly string $url,
    ) {}
}
