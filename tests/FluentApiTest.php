<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Torqie\LaravelPasswordless\Events\LoginCodeSent;
use Torqie\LaravelPasswordless\Events\MagicLinkSent;
use Torqie\LaravelPasswordless\Facades\LaravelPasswordless;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Notifications\LoginCodeNotification;
use Torqie\LaravelPasswordless\Notifications\MagicLinkNotification;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'fluent@example.com']);
});

// -------------------------------------------------------------------------
// sendMagicLink
// -------------------------------------------------------------------------

it('sendMagicLink returns a signed URL string', function () {
    Notification::fake();

    $url = LaravelPasswordless::for($this->user)->sendMagicLink();

    expect($url)->toBeString()->toContain('/auth/magic-link/');
});

it('sendMagicLink sends a MagicLinkNotification to the user', function () {
    Notification::fake();

    LaravelPasswordless::for($this->user)->sendMagicLink();

    Notification::assertSentTo($this->user, MagicLinkNotification::class);
});

it('sendMagicLink creates a token in the database', function () {
    Notification::fake();

    LaravelPasswordless::for($this->user)->sendMagicLink();

    expect(PasswordlessToken::count())->toBe(1);
    expect(PasswordlessToken::first()->type)->toBe('magic_link');
});

it('sendMagicLink fires the MagicLinkSent event', function () {
    Notification::fake();
    Event::fake();

    LaravelPasswordless::for($this->user)->sendMagicLink();

    Event::assertDispatched(MagicLinkSent::class, function ($event) {
        return $event->authenticatable->id === $this->user->id;
    });
});

// -------------------------------------------------------------------------
// sendLoginCode
// -------------------------------------------------------------------------

it('sendLoginCode returns the plain-text code', function () {
    Notification::fake();

    $code = LaravelPasswordless::for($this->user)->sendLoginCode();

    expect($code)->toBeString()->not->toBeEmpty();
});

it('sendLoginCode sends a LoginCodeNotification to the user', function () {
    Notification::fake();

    LaravelPasswordless::for($this->user)->sendLoginCode();

    Notification::assertSentTo($this->user, LoginCodeNotification::class);
});

it('sendLoginCode creates a token in the database', function () {
    Notification::fake();

    LaravelPasswordless::for($this->user)->sendLoginCode();

    expect(PasswordlessToken::count())->toBe(1);
    expect(PasswordlessToken::first()->type)->toBe('login_code');
});

it('sendLoginCode fires the LoginCodeSent event', function () {
    Notification::fake();
    Event::fake();

    LaravelPasswordless::for($this->user)->sendLoginCode();

    Event::assertDispatched(LoginCodeSent::class, function ($event) {
        return $event->authenticatable->id === $this->user->id;
    });
});

// -------------------------------------------------------------------------
// Guard rails
// -------------------------------------------------------------------------

it('throws a LogicException if sendMagicLink is called without for()', function () {
    Notification::fake();

    expect(fn () => LaravelPasswordless::sendMagicLink())
        ->toThrow(\LogicException::class);
});

it('throws a LogicException if sendLoginCode is called without for()', function () {
    Notification::fake();

    expect(fn () => LaravelPasswordless::sendLoginCode())
        ->toThrow(\LogicException::class);
});

// -------------------------------------------------------------------------
// Isolation — for() returns a clone, not the singleton
// -------------------------------------------------------------------------

it('for() does not mutate the facade singleton', function () {
    Notification::fake();

    LaravelPasswordless::for($this->user)->sendMagicLink();

    expect(fn () => LaravelPasswordless::sendMagicLink())
        ->toThrow(\LogicException::class);
});

