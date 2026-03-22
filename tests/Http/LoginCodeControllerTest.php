<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Wiredrhino\LaravelPasswordless\Events\LoginCodeSent;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;
use Wiredrhino\LaravelPasswordless\Notifications\LoginCodeNotification;
use Wiredrhino\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'code@example.com']);
});

// -------------------------------------------------------------------------
// GET /auth/code
// -------------------------------------------------------------------------

it('shows the code request form', function () {
    $this->get(route('passwordless.login-code.request'))
        ->assertOk()
        ->assertViewIs('laravel-passwordless::login-code.request');
});

// -------------------------------------------------------------------------
// POST /auth/code
// -------------------------------------------------------------------------

it('sends a LoginCodeNotification and redirects to verify', function () {
    Notification::fake();

    $this->post(route('passwordless.login-code.send'), ['email' => $this->user->email])
        ->assertRedirect(route('passwordless.login-code.verify'));

    Notification::assertSentTo($this->user, LoginCodeNotification::class);
});

it('stores the email in session after sending', function () {
    Notification::fake();

    $this->post(route('passwordless.login-code.send'), ['email' => $this->user->email]);

    expect(session('passwordless.pending_email'))->toBe($this->user->email);
});

it('fires the LoginCodeSent event', function () {
    Notification::fake();
    Event::fake();

    $this->post(route('passwordless.login-code.send'), ['email' => $this->user->email]);

    Event::assertDispatched(LoginCodeSent::class, function ($event) {
        return $event->authenticatable->id === $this->user->id;
    });
});

it('redirects to verify without sending for an unknown email', function () {
    Notification::fake();

    $this->post(route('passwordless.login-code.send'), ['email' => 'ghost@example.com'])
        ->assertRedirect(route('passwordless.login-code.verify'));

    Notification::assertNothingSent();
});

it('fails validation for a missing email', function () {
    $this->post(route('passwordless.login-code.send'), [])
        ->assertSessionHasErrors('email');
});

// -------------------------------------------------------------------------
// GET /auth/code/verify
// -------------------------------------------------------------------------

it('shows the verify form when a pending email is in session', function () {
    $this->withSession(['passwordless.pending_email' => $this->user->email])
        ->get(route('passwordless.login-code.verify'))
        ->assertOk()
        ->assertViewIs('laravel-passwordless::login-code.verify')
        ->assertSee($this->user->email);
});

it('redirects to request when there is no pending email in session', function () {
    $this->get(route('passwordless.login-code.verify'))
        ->assertRedirect(route('passwordless.login-code.request'));
});

// -------------------------------------------------------------------------
// POST /auth/code/verify
// -------------------------------------------------------------------------

it('authenticates the user with a valid code', function () {
    $plain = '654321';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $this->withSession(['passwordless.pending_email' => $this->user->email])
        ->post(route('passwordless.login-code.authenticate'), ['code' => $plain])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($this->user);
});

it('clears the pending email from session on success', function () {
    $plain = '111222';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $this->withSession(['passwordless.pending_email' => $this->user->email])
        ->post(route('passwordless.login-code.authenticate'), ['code' => $plain]);

    expect(session('passwordless.pending_email'))->toBeNull();
});

it('returns a validation error for a wrong code', function () {
    $this->withSession(['passwordless.pending_email' => $this->user->email])
        ->post(route('passwordless.login-code.authenticate'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

it('redirects to request when there is no session on verify POST', function () {
    $this->post(route('passwordless.login-code.authenticate'), ['code' => '123456'])
        ->assertRedirect(route('passwordless.login-code.request'));
});
