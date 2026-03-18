<?php

// config for Wiredrhino/LaravelPasswordless
return [

    /*
    |--------------------------------------------------------------------------
    | Token Type
    |--------------------------------------------------------------------------
    | Controls which authentication flow(s) are available.
    | Options: 'magic_link', 'login_code', 'both'
    */
    'type' => env('PASSWORDLESS_TYPE', 'both'),

    /*
    |--------------------------------------------------------------------------
    | Token TTL (minutes)
    |--------------------------------------------------------------------------
    | How long a magic link or login code remains valid after being issued.
    */
    'ttl' => (int) env('PASSWORDLESS_TTL', 15),

    /*
    |--------------------------------------------------------------------------
    | Login Code Settings
    |--------------------------------------------------------------------------
    */
    'code' => [
        'length'  => (int) env('PASSWORDLESS_CODE_LENGTH', 6),
        'charset' => env('PASSWORDLESS_CODE_CHARSET', '0123456789'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    */
    'guard' => env('PASSWORDLESS_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    */
    'user_model' => env('PASSWORDLESS_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Redirect Paths
    |--------------------------------------------------------------------------
    */
    'redirects' => [
        'after_login'   => env('PASSWORDLESS_REDIRECT_AFTER_LOGIN', '/dashboard'),
        'invalid_token' => env('PASSWORDLESS_REDIRECT_INVALID', '/login'),
    ],

];
