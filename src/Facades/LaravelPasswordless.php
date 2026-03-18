<?php

namespace Wiredrhino\LaravelPasswordless\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wiredrhino\LaravelPasswordless\LaravelPasswordless
 */
class LaravelPasswordless extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Wiredrhino\LaravelPasswordless\LaravelPasswordless::class;
    }
}
