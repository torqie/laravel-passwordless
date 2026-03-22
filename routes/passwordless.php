<?php

use Illuminate\Support\Facades\Route;
use Wiredrhino\LaravelPasswordless\Http\Controllers\LoginCodeController;
use Wiredrhino\LaravelPasswordless\Http\Controllers\MagicLinkController;

$prefix     = config('passwordless.routes.prefix', 'auth');
$middleware = config('passwordless.routes.middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        // -----------------------------------------------------------------------
        // Magic Link
        // -----------------------------------------------------------------------
        Route::get('magic-link', [MagicLinkController::class, 'request'])
            ->name('passwordless.magic-link.request');

        Route::post('magic-link', [MagicLinkController::class, 'send'])
            ->name('passwordless.magic-link.send');

        Route::get('magic-link/{token}', [MagicLinkController::class, 'authenticate'])
            ->name('passwordless.magic-link.authenticate')
            ->middleware('passwordless.signed');

        // -----------------------------------------------------------------------
        // Login Code (OTP)
        // -----------------------------------------------------------------------
        Route::get('code', [LoginCodeController::class, 'request'])
            ->name('passwordless.login-code.request');

        Route::post('code', [LoginCodeController::class, 'send'])
            ->name('passwordless.login-code.send');

        Route::get('code/verify', [LoginCodeController::class, 'verify'])
            ->name('passwordless.login-code.verify');

        Route::post('code/verify', [LoginCodeController::class, 'authenticate'])
            ->name('passwordless.login-code.authenticate');
    });



