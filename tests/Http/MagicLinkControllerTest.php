<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Wiredrhino\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Wiredrhino\LaravelPasswordless\Events\MagicLinkSent;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;
use Wiredrhino\LaravelPasswordless\Notifications\MagicLinkNotification;
use Wiredrhino\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'magic@example.com']);
});

// -------------------------------------------------------------------------
// GET /auth/magic-link
// -------------------------------------------------------------------------

it('shows the request form', function () {
    $this->get(route('passwordless.magic-link.request'))
        ->assertOk()
        ->assertViewIs('laravel-passwordless::magic-link.request');
});

// -------------------------------------------------------------------------
// POST /auth/magic-link
// -------------------------------------------------------------------------

it('sends a MagicLinkNotification to the user', function () {
    Notification::fake();

    $this->post(route('passwordless.magic-link.send'), ['email' => $this->user->email])
        ->assertOk()
        ->assertViewIs('laravel-passwordless::magic-link.sent');

    Notification::assertSentTo($this->user, MagicLinkNotification::class);
});

it('creates a token in the database after sending', function () {
    Notification::fake();

    $this->post(route('passwordless.magic-link.send'), ['email' => $this->user->email]);

    expect(PasswordlessToken::count())->toBe(1);
    expect(PasswordlessToken::first()->type)->toBe('magic_link');
});

it('fires the MagicLinkSent event', function () {
    Notification::fake();
    Event::fake();

    $this->post(route('passwordless.magic-link.send'), ['email' => $this->user->email]);

    Event::assertDispatched(MagicLinkSent::class, function ($event) {
        return $event->authenticatable->id === $this->user->id;
    });
});

it('shows the sent view even for an unknown email (no enumeration)', function () {
    Notification::fake();

    $this->post(route('passwordless.magic-link.send'), ['email' => 'ghost@example.com'])
        ->assertOk()
        ->assertViewIs('laravel-passwordless::magic-link.sent');

    Notification::assertNothingSent();
    expect(PasswordlessToken::count())->toBe(0);
});

it('fails validation for an invalid email format', function () {
    $this->post(route('passwordless.magic-link.send'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

// -------------------------------------------------------------------------
// GET /auth/magic-link/{token}  (signed)
// -------------------------------------------------------------------------

it('authenticates the user via a valid signed URL and redirects to after_login', function () {
    $url = app(GenerateMagicLinkAction::class)
        ->generate($this->user);

    $this->get($url)
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($this->user);
});

it('marks the token as used after a successful click-through', function () {
    $url = app(GenerateMagicLinkAction::class)
        ->generate($this->user);

    $this->get($url);

    expect(PasswordlessToken::first()->used_at)->not->toBeNull();
});

it('redirects to invalid_token when the signature is tampered with', function () {
    $url = app(GenerateMagicLinkAction::class)
        ->generate($this->user);

    // Tamper with the signature
    $this->get($url.'tampered')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('redirects to invalid_token when the token has already been used', function () {
    $url = app(GenerateMagicLinkAction::class)
        ->generate($this->user);

    // First click — marks token as used
    $this->get($url);

    // Second click — should fail
    $this->get($url)
        ->assertRedirect('/login');
});
