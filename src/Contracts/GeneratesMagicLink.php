<?php

namespace Torqie\LaravelPasswordless\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface GeneratesMagicLink
{
    /**
     * Generate a magic link URL for the given authenticatable.
     */
    public function generate(Authenticatable $authenticatable): string;
}
