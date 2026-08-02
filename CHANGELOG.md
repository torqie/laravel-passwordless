# Changelog

All notable changes to `laravel-passwordless` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2026-08-02

### Fixed

- `config('passwordless.actions.*')` now actually applies to the HTTP flow. Both controllers type-hinted the concrete action classes in their method signatures, so the container returned the default implementation and the configured class was bypassed — the bindings only ever took effect through the facade. The controllers now type-hint the contracts (`GeneratesMagicLink`, `AuthenticatesViaMagicLink`, `GeneratesLoginCode`, `AuthenticatesViaLoginCode`).

### Added

- `Torqie\LaravelPasswordless\Contracts\ResolvesUserForSend` — `handle(string $email, string $ip): ?Authenticatable`. `ResolveUserForSendAction` now implements it, and the controllers type-hint the contract.
- `actions.resolve_user` config key, bound in the service provider alongside the other four. This is the extension point for deciding what happens when the submitted email has no account: the default returns `null` (the flow stays silent to prevent user-enumeration), and an override can register the address instead, which makes passwordless sign-up possible without patching the package. Call `parent::handle()` from a subclass to keep the send rate limiting.
- Controller-level tests that swap each of the five contracts via config and assert the replacement runs through a real HTTP request.

### Upgrade notes

Nothing to change if you use the defaults. If you worked around the bug by binding the **concrete** action classes in your own service provider, switch to the `actions.*` config keys — those container bindings still work, but they are no longer needed.

## Initial Release - 2026-03-23

### 🎉 Initial Release

Passwordless authentication for Laravel via **magic links** and **login codes** (OTP). No passwords, no complexity , just click a link or type a code.

#### What's included

##### Magic Link Flow

- Generate a signed, time-limited URL and email it to the user
- Click the link to authenticate instantly
- Built-in controller, routes, and Blade views

##### Login Code (OTP) Flow

- Generate a short numeric/alphanumeric code and email it to the user
- Submit the code on a verify form to authenticate
- Rate limited to 5 failed attempts per email address

##### Fluent API

```php
LaravelPasswordless::for($user)->sendMagicLink();
LaravelPasswordless::for($user)->sendLoginCode();

```
##### Core

- `PasswordlessToken` Eloquent model with scopes and helpers
- `HasPasswordlessAuth` trait for your User model
- Three events: `MagicLinkSent`, `LoginCodeSent`, `UserAuthenticatedPasswordlessly`
- `passwordless:purge` Artisan command to clean up expired/used tokens
- Fully configurable via `config/passwordless.php` Swap views, action classes, TTL, guard, redirects, and more

##### Requirements

- PHP 8.4+
- Laravel 11+

## [1.0.0] - 2026-03-21

### Added

#### Magic Link Flow

- `GenerateMagicLinkAction` — creates a hashed `PasswordlessToken` record and builds a signed, time-limited URL via `URL::temporarySignedRoute`
- `AuthenticateViaMagicLinkAction` — validates the signed URL, looks up and verifies the token (not expired, not used), logs the user in via the configured guard, and marks the token used
- `MagicLinkNotification` — mailable notification delivering the magic link URL to the user
- `MagicLinkController` — HTTP controller handling `GET /auth/magic-link` (request form), `POST /auth/magic-link` (send email), and `GET /auth/magic-link/{token}` (click-through authentication)
- Built-in Blade views: `magic-link/request`, `magic-link/sent`, `emails/magic-link`

#### Login Code (OTP) Flow

- `GenerateLoginCodeAction` — creates a hashed `PasswordlessToken` of type `login_code` and returns the plain-text code once
- `AuthenticateViaLoginCodeAction` — accepts an email and submitted code, hashes and compares against the stored token, enforces rate limiting (5 failed attempts per email via Laravel's `RateLimiter`), logs the user in, and marks the token used
- `LoginCodeNotification` — mailable notification delivering the OTP code to the user
- `LoginCodeController` — HTTP controller handling `GET /auth/code` (request form), `POST /auth/code` (send code), `GET /auth/code/verify` (code entry form), and `POST /auth/code/verify` (authenticate)
- Built-in Blade views: `login-code/request`, `login-code/verify`, `emails/login-code`

#### Core

- `PasswordlessToken` Eloquent model with `valid()`, `unused()`, and `ofType()` scopes, plus `isExpired()`, `isUsed()`, and `markUsed()` helper methods
- `HasPasswordlessAuth` trait for User models — exposes `passwordlessTokens`, `validPasswordlessTokens`, and `invalidatePasswordlessTokens()` helpers
- `LaravelPasswordless` fluent class and `LaravelPasswordless` facade — `LaravelPasswordless::for($user)->sendMagicLink()` and `LaravelPasswordless::for($user)->sendLoginCode()`
- `TokenGenerator` support class for secure random token and code generation
- `SignedUrlBuilder` support class for constructing signed magic link URLs

#### Events

- `MagicLinkSent` — fired after a magic link is generated and emailed; carries `$authenticatable` and `$url`
- `LoginCodeSent` — fired after a login code is generated and emailed; carries `$authenticatable`
- `UserAuthenticatedPasswordlessly` — fired after a successful passwordless login; carries `$authenticatable` and `$type` (`magic_link` | `login_code`)

#### Infrastructure

- `passwordless.signed` middleware alias — validates signed URL signature and expiry on the magic link authenticate route
- `PurgePasswordlessTokensCommand` (`php artisan passwordless:purge`) — removes expired and/or used tokens; supports `--expired` and `--used` flags
- `config/passwordless.php` — fully documented configuration file covering token type, TTL, code settings, guard, user model, redirect paths, route prefix/middleware, view overrides, and action class bindings
- Database migration for the `passwordless_tokens` table
- All routes, views, config, and migrations publishable via `vendor:publish` tags
- Full Pest v4 test suite — 102 tests covering models, actions, controllers, notifications, events, the fluent API, and the purge command
- PHPStan / Larastan level 8 static analysis with baseline
