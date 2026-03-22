# Changelog

All notable changes to `laravel-passwordless` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

