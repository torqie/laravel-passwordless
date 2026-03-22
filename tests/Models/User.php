<?php

namespace Torqie\LaravelPasswordless\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Torqie\LaravelPasswordless\Traits\HasPasswordlessAuth;

class User extends Authenticatable
{
    use HasPasswordlessAuth;
    use Notifiable;

    protected $guarded = [];
}
