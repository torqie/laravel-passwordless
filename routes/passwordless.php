<?php

use Illuminate\Support\Facades\Route;
use Wiredrhino\LaravelPasswordless\Http\Controllers\MagicLinkController;

$prefix     = config('passwordless.routes.prefix', 'auth');
$middleware = config('passwordless.routes.middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        Route::get('magic-link', [MagicLinkController::class, 'request'])
            ->name('passwordless.magic-link.request');

        Route::post('magic-link', [MagicLinkController::class, 'send'])
            ->name('passwordless.magic-link.send');

        Route::get('magic-link/{token}', [MagicLinkController::class, 'authenticate'])
            ->name('passwordless.magic-link.authenticate');
    });

