<?php

namespace Wiredrhino\LaravelPasswordless\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class UserAuthenticatedPasswordlessly
{
    public function __construct(
        public readonly Authenticatable $authenticatable,
        /** @var 'magic_link'|'login_code' */
        public readonly string $type,
    ) {}
}
