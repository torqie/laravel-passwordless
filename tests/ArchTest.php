<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('all action classes exist and are concrete classes')
    ->expect('Torqie\LaravelPasswordless\Actions')
    ->classes()
    ->toBeClasses();

arch('models extend Eloquent Model')
    ->expect('Torqie\LaravelPasswordless\Models')
    ->toExtend(\Illuminate\Database\Eloquent\Model::class);

arch('controllers extend the base controller')
    ->expect('Torqie\LaravelPasswordless\Http\Controllers')
    ->toExtend(\Illuminate\Routing\Controller::class);

arch('notifications extend the base notification')
    ->expect('Torqie\LaravelPasswordless\Notifications')
    ->toExtend(\Illuminate\Notifications\Notification::class);

arch('events are plain data classes with no side-effects')
    ->expect('Torqie\LaravelPasswordless\Events')
    ->classes()
    ->toBeClasses()
    ->not->toExtend(\Illuminate\Broadcasting\Channel::class);

arch('support classes do not extend Eloquent Model')
    ->expect('Torqie\LaravelPasswordless\Support')
    ->not->toExtend(\Illuminate\Database\Eloquent\Model::class);

arch('contracts contain only interfaces')
    ->expect('Torqie\LaravelPasswordless\Contracts')
    ->toBeInterfaces();
