<?php

namespace Wiredrhino\LaravelPasswordless\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Wiredrhino\LaravelPasswordless\LaravelPasswordless
 *
 * @method static \Wiredrhino\LaravelPasswordless\LaravelPasswordless for(Authenticatable $user)
 * @method static string sendMagicLink()
 * @method static string sendLoginCode()
 */
class LaravelPasswordless extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Wiredrhino\LaravelPasswordless\LaravelPasswordless::class;
    }
}
