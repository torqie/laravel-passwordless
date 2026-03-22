<?php

namespace Torqie\LaravelPasswordless\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface GeneratesLoginCode
{
    /**
     * Generate a one-time login code for the given authenticatable
     * and return the plain-text code exactly once.
     */
    public function generate(Authenticatable $authenticatable): string;
}

