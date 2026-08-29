<?php

use App\Models\User;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction;
use Torqie\LaravelPasswordless\Actions\GenerateLoginCodeAction;
use Torqie\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Torqie\LaravelPasswordless\Actions\ResolveUserForSendAction;

// config for Torqie/LaravelPasswordless
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
        'length' => (int) env('PASSWORDLESS_CODE_LENGTH', 6),
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
    'user_model' => env('PASSWORDLESS_USER_MODEL', User::class),

    /*
    |--------------------------------------------------------------------------
    | Remember Me
    |--------------------------------------------------------------------------
    | Whether to create a persistent "remember me" session when the user
    | authenticates via a magic link or login code.
    */
    'remember' => env('PASSWORDLESS_REMEMBER', false),

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    | 'send'   – max send requests (per IP, per minute) before throttling.
    | 'verify' – max failed code attempts (per email, per TTL window) before
    |             the user must wait.
    */
    'rate_limits' => [
        'send' => (int) env('PASSWORDLESS_RATE_LIMIT_SEND', 5),
        'verify' => (int) env('PASSWORDLESS_RATE_LIMIT_VERIFY', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect Paths
    |--------------------------------------------------------------------------
    */
    'redirects' => [
        'after_login' => env('PASSWORDLESS_REDIRECT_AFTER_LOGIN', '/dashboard'),
        'invalid_token' => env('PASSWORDLESS_REDIRECT_INVALID', '/login'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Prefix and middleware applied to all passwordless routes.
    */
    'routes' => [
        'prefix' => env('PASSWORDLESS_ROUTE_PREFIX', 'auth'),
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    | Override any view by pointing to your own. Leave null to use the
    | package defaults. Alternatively, publish the views with:
    |   php artisan vendor:publish --tag="laravel-passwordless-views"
    */
    'views' => [
        'magic_link_request' => null, // e.g. 'auth.magic-link.request'
        'magic_link_sent' => null, // e.g. 'auth.magic-link.sent'
        'magic_link_email' => null, // e.g. 'emails.magic-link'
        'login_code_request' => null, // e.g. 'auth.login-code.request'
        'login_code_verify' => null, // e.g. 'auth.login-code.verify'
        'login_code_email' => null, // e.g. 'emails.login-code'
    ],

    /*
    |--------------------------------------------------------------------------
    | Inertia
    |--------------------------------------------------------------------------
    | Set 'inertia' to true if your app uses Inertia.js. Define the component
    | names below — these will be passed to Inertia::render() instead of
    | returning a Blade view. The 'components' values take precedence over
    | 'views' when Inertia is enabled.
    */
    'inertia' => env('PASSWORDLESS_INERTIA', false),

    'components' => [
        'magic_link_request' => null, // e.g. 'Auth/MagicLinkRequest'
        'magic_link_sent' => null,    // e.g. 'Auth/MagicLinkSent'
        'login_code_request' => null, // e.g. 'Auth/LoginCodeRequest'
        'login_code_verify' => null,  // e.g. 'Auth/LoginCodeVerify'
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Bindings
    |--------------------------------------------------------------------------
    | Swap any action with your own implementation by pointing to a class
    | that implements the corresponding contract.
    |
    | 'resolve_user' decides who receives a token when someone submits the
    | send form. The default throttles the request and returns the matching
    | user, or null when the email is unknown (the flow then stays silent to
    | avoid user-enumeration). Point this at your own ResolvesUserForSend
    | implementation to change that — e.g. to register a new account instead.
    */
    'actions' => [
        'generate_magic_link' => GenerateMagicLinkAction::class,
        'authenticate_magic_link' => AuthenticateViaMagicLinkAction::class,
        'generate_login_code' => GenerateLoginCodeAction::class,
        'authenticate_login_code' => AuthenticateViaLoginCodeAction::class,
        'resolve_user' => ResolveUserForSendAction::class,
    ],

];
