# laravel-passwordless

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wiredrhino/laravel-passwordless.svg?style=flat-square)](https://packagist.org/packages/wiredrhino/laravel-passwordless)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wiredrhino/laravel-passwordless/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/wiredrhino/laravel-passwordless/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/wiredrhino/laravel-passwordless/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/wiredrhino/laravel-passwordless/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wiredrhino/laravel-passwordless.svg?style=flat-square)](https://packagist.org/packages/wiredrhino/laravel-passwordless)

Passwordless authentication for Laravel via **magic links** and **login codes** (OTP). No passwords, no complexity — just click a link or type a code.

- 🔗 **Magic links** — a signed, time-limited URL sent by email; click to log in instantly
- 🔢 **Login codes** — a short numeric/alphanumeric code sent by email; enter it on a verify form
- 🔄 Use one flow, the other, or both at the same time
- 🎛 Fully customisable — swap views, swap action classes, configure everything via `config/passwordless.php`

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.4+ |
| Laravel | 11 or 12 |

---

## Installation

Install via Composer:

```bash
composer require wiredrhino/laravel-passwordless
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="laravel-passwordless-migrations"
php artisan migrate
```

Publish the config file (optional but recommended):

```bash
php artisan vendor:publish --tag="laravel-passwordless-config"
```

Optionally publish the views to customize them:

```bash
php artisan vendor:publish --tag="laravel-passwordless-views"
```

---

## Setup

### 1. Add the trait to your User model

```php
use Wiredrhino\LaravelPasswordless\Traits\HasPasswordlessAuth;

class User extends Authenticatable
{
    use HasPasswordlessAuth;
    // ...
}
```

### 2. Configure your user model (if not `App\Models\User`)

```php
// config/passwordless.php
'user_model' => App\Models\User::class,
```

Or via `.env`:

```dotenv
PASSWORDLESS_USER_MODEL=App\Models\User
```

### 3. Routes are registered automatically

The package registers all routes under the configured prefix (default: `/auth`). No additional route registration is needed.

---

## Usage

### Using the built-in routes

The package ships with ready-made routes and controllers for both flows. After installation you get these routes out of the box:

| Method | URI | Name | Description |
|---|---|---|---|
| `GET` | `/auth/magic-link` | `passwordless.magic-link.request` | Show email form |
| `POST` | `/auth/magic-link` | `passwordless.magic-link.send` | Send magic link email |
| `GET` | `/auth/magic-link/{token}` | `passwordless.magic-link.authenticate` | Authenticate via clicked link |
| `GET` | `/auth/code` | `passwordless.login-code.request` | Show email form |
| `POST` | `/auth/code` | `passwordless.login-code.send` | Send login code email |
| `GET` | `/auth/code/verify` | `passwordless.login-code.verify` | Show code entry form |
| `POST` | `/auth/code/verify` | `passwordless.login-code.authenticate` | Authenticate via submitted code |

Link to either flow from your login page:

```html
<a href="{{ route('passwordless.magic-link.request') }}">Sign in with a magic link</a>
<a href="{{ route('passwordless.login-code.request') }}">Sign in with a code</a>
```

### Using the fluent API (programmatic)

Use the `LaravelPasswordless` facade when you need to trigger a magic link or login code from your own code (e.g. inside a controller, job, or listener):

```php
use Wiredrhino\LaravelPasswordless\Facades\LaravelPasswordless;

// Send a magic link — returns the signed URL
$url = LaravelPasswordless::for($user)->sendMagicLink();

// Send a login code — returns the plain-text code
$code = LaravelPasswordless::for($user)->sendLoginCode();
```

`sendMagicLink()` and `sendLoginCode()` both:
1. Generate and persist a hashed token
2. Send the appropriate notification to the user
3. Fire the corresponding event (`MagicLinkSent` / `LoginCodeSent`)

### Using the trait helpers directly

The `HasPasswordlessAuth` trait exposes helpers on your User model:

```php
// All tokens associated with the user
$user->passwordlessTokens;

// Only valid (non-expired, non-used) tokens
$user->validPasswordlessTokens;

// Invalidate all unused tokens (optional: scope to one type)
$user->invalidatePasswordlessTokens();
$user->invalidatePasswordlessTokens('magic_link');
$user->invalidatePasswordlessTokens('login_code');
```

---

## Configuration

All options live in `config/passwordless.php`.

### `type`

Which flow(s) to make available. This is informational for your own UI — the package does not restrict routes by this setting.

```php
'type' => 'both', // 'magic_link' | 'login_code' | 'both'
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_TYPE` | `both` |

---

### `ttl`

How many minutes a magic link or login code remains valid after being issued.

```php
'ttl' => 15,
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_TTL` | `15` |

---

### `code`

Settings specific to the login code (OTP) flow.

```php
'code' => [
    'length'  => 6,            // Number of characters in the code
    'charset' => '0123456789', // Characters to draw from
],
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_CODE_LENGTH` | `6` |
| `PASSWORDLESS_CODE_CHARSET` | `0123456789` |

You can make codes alphanumeric:

```dotenv
PASSWORDLESS_CODE_CHARSET=ABCDEFGHJKLMNPQRSTUVWXYZ23456789
PASSWORDLESS_CODE_LENGTH=8
```

---

### `guard`

The authentication guard used when logging the user in.

```php
'guard' => 'web',
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_GUARD` | `web` |

---

### `user_model`

The fully-qualified class name of the authenticatable model to look up by email.

```php
'user_model' => \App\Models\User::class,
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_USER_MODEL` | `App\Models\User` |

---

### `redirects`

Where to send the user after a successful login or when a token is invalid/expired.

```php
'redirects' => [
    'after_login'   => '/dashboard',
    'invalid_token' => '/login',
],
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_REDIRECT_AFTER_LOGIN` | `/dashboard` |
| `PASSWORDLESS_REDIRECT_INVALID` | `/login` |

---

### `routes`

Prefix and middleware applied to all passwordless routes.

```php
'routes' => [
    'prefix'     => 'auth',
    'middleware' => ['web'],
],
```

| `.env` key | Default |
|---|---|
| `PASSWORDLESS_ROUTE_PREFIX` | `auth` |

Change the prefix to mount the routes under `/login`:

```php
'routes' => [
    'prefix'     => 'login',
    'middleware' => ['web'],
],
```

---

### `views`

Override any view by pointing to your own. Set a key to a view string to use it instead of the package default. Leave as `null` to use the package default.

```php
'views' => [
    'magic_link_request' => null,  // GET /auth/magic-link
    'magic_link_sent'    => null,  // after POST /auth/magic-link
    'magic_link_email'   => null,  // email notification
    'login_code_request' => null,  // GET /auth/code
    'login_code_verify'  => null,  // GET /auth/code/verify
    'login_code_email'   => null,  // email notification
],
```

Example — use your own Blade view for the magic link email:

```php
'views' => [
    'magic_link_email' => 'emails.auth.magic-link',
],
```

Your view receives these variables:

| Flow | Variable | Type | Description |
|---|---|---|---|
| Magic link | `$url` | `string` | The fully-signed magic link URL |
| Magic link | `$expiresMins` | `int` | TTL in minutes |
| Login code | `$code` | `string` | The plain-text OTP code |
| Login code | `$expiresMins` | `int` | TTL in minutes |

Alternatively, publish the built-in views and edit them in your project:

```bash
php artisan vendor:publish --tag="laravel-passwordless-views"
# → resources/views/vendor/laravel-passwordless/
```

---

### `actions`

Swap any action class with your own implementation. Your class must implement the corresponding contract from `Wiredrhino\LaravelPasswordless\Contracts`.

```php
'actions' => [
    'generate_magic_link'     => \App\Auth\MyGenerateMagicLinkAction::class,
    'authenticate_magic_link' => \Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction::class,
    'generate_login_code'     => \Wiredrhino\LaravelPasswordless\Actions\GenerateLoginCodeAction::class,
    'authenticate_login_code' => \Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction::class,
],
```

---

## Customisation

### Swapping an action class

1. Create a class that implements the relevant contract:

```php
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesMagicLink;

class MyGenerateMagicLinkAction implements GeneratesMagicLink
{
    public function generate(\Illuminate\Contracts\Auth\Authenticatable $authenticatable): string
    {
        // your logic
    }
}
```

2. Register it in `config/passwordless.php`:

```php
'actions' => [
    'generate_magic_link' => \App\Auth\MyGenerateMagicLinkAction::class,
],
```

### Listening to events

The package dispatches three events you can listen to in your `EventServiceProvider` or using `#[Listen]` attributes:

| Event | Fired when | Properties |
|---|---|---|
| `MagicLinkSent` | A magic link is generated and emailed | `$authenticatable`, `$url` |
| `LoginCodeSent` | A login code is generated and emailed | `$authenticatable` |
| `UserAuthenticatedPasswordlessly` | A user successfully logs in | `$authenticatable`, `$type` (`magic_link` \| `login_code`) |

```php
use Wiredrhino\LaravelPasswordless\Events\UserAuthenticatedPasswordlessly;

class LogPasswordlessLogin
{
    public function handle(UserAuthenticatedPasswordlessly $event): void
    {
        activity()
            ->causedBy($event->authenticatable)
            ->log("Logged in via {$event->type}");
    }
}
```

### Rate limiting

Login code verification is rate-limited at **5 failed attempts per email address** using Laravel's `RateLimiter`. Exceeding the limit returns a `ValidationException` with a countdown message. The counter is cleared automatically on a successful authentication.

### The `passwordless.signed` middleware

The magic link authenticate route is protected by the `passwordless.signed` middleware alias, which validates the signed URL signature and expiry. You can apply this middleware to your own routes if needed:

```php
Route::get('/my-route/{token}', MyController::class)->middleware('passwordless.signed');
```

---

## Artisan Commands

### `passwordless:purge`

Remove expired and/or used tokens from the database. Run this periodically (e.g. via the scheduler) to keep the `passwordless_tokens` table clean.

```bash
# Purge everything expired or used (default)
php artisan passwordless:purge

# Only remove expired tokens
php artisan passwordless:purge --expired

# Only remove used (but not yet expired) tokens
php artisan passwordless:purge --used
```

Schedule it in `routes/console.php`:

```php
Schedule::command('passwordless:purge')->daily();
```

---

## Testing

```bash
composer test
```

The package ships with a full Pest test suite — 102 tests covering models, actions, controllers, notifications, events, the fluent API, and the purge command.

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Terik Hone](https://github.com/torqie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
