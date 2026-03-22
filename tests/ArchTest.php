<?php

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Routing\Controller;

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('all action classes exist and are concrete classes')
    ->expect('Wiredrhino\LaravelPasswordless\Actions')
    ->classes()
    ->toBeClasses();

arch('models extend Eloquent Model')
    ->expect('Wiredrhino\LaravelPasswordless\Models')
    ->toExtend(Model::class);

arch('controllers extend the base controller')
    ->expect('Wiredrhino\LaravelPasswordless\Http\Controllers')
    ->toExtend(Controller::class);

arch('notifications extend the base notification')
    ->expect('Wiredrhino\LaravelPasswordless\Notifications')
    ->toExtend(Notification::class);

arch('events are plain data classes with no side-effects')
    ->expect('Wiredrhino\LaravelPasswordless\Events')
    ->classes()
    ->toBeClasses()
    ->not->toExtend(Channel::class);

arch('support classes do not extend Eloquent Model')
    ->expect('Wiredrhino\LaravelPasswordless\Support')
    ->not->toExtend(Model::class);

arch('contracts contain only interfaces')
    ->expect('Wiredrhino\LaravelPasswordless\Contracts')
    ->toBeInterfaces();
