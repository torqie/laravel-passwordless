<?php

namespace Wiredrhino\LaravelPasswordless\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Wiredrhino\LaravelPasswordless\Traits\HasPasswordlessAuth;

class User extends Authenticatable
{
    use HasPasswordlessAuth;
    use Notifiable;

    protected $guarded = [];
}
