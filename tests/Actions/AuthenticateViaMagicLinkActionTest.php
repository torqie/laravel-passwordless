<?php

use Illuminate\Support\Facades\Event;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction;
use Torqie\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Torqie\LaravelPasswordless\Events\UserAuthenticatedPasswordlessly;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user   = User::create(['email' => 'test@example.com']);
    $this->action = app(AuthenticateViaMagicLinkAction::class);
});

// -------------------------------------------------------------------------
// Token lookup
// -------------------------------------------------------------------------

it('redirects to invalid_token when the token does not exist', function () {
    $response = $this->action->authenticate('no-such-token');

    expect($response->getTargetUrl())->toContain('/login');
});

it('redirects to invalid_token when the token is expired', function () {
    $plain = 'plain-expired';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $this->user->id,
        'token'                => hash('sha256', $plain),
        'type'                 => 'magic_link',
        'expires_at'           => now()->subMinute(),
        'used_at'              => null,
    ]);

    $response = $this->action->authenticate($plain);

    expect($response->getTargetUrl())->toContain('/login');
});

it('redirects to invalid_token when the token is already used', function () {
    $plain = 'plain-used';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $this->user->id,
        'token'                => hash('sha256', $plain),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => now()->subSecond(),
    ]);

    $response = $this->action->authenticate($plain);

    expect($response->getTargetUrl())->toContain('/login');
});

// -------------------------------------------------------------------------
// Success path
// -------------------------------------------------------------------------

it('redirects to after_login on a valid token', function () {
    $plain = 'plain-valid';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $this->user->id,
        'token'                => hash('sha256', $plain),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => null,
    ]);

    $response = $this->action->authenticate($plain);

    expect($response->getTargetUrl())->toContain('/dashboard');
});

it('marks the token as used after authentication', function () {
    $plain = 'plain-mark-used';
    $token = PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $this->user->id,
        'token'                => hash('sha256', $plain),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => null,
    ]);

    $this->action->authenticate($plain);

    expect($token->fresh()->used_at)->not->toBeNull();
});

it('fires the UserAuthenticatedPasswordlessly event with type magic_link', function () {
    Event::fake();

    $plain = 'plain-event';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $this->user->id,
        'token'                => hash('sha256', $plain),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => null,
    ]);

    $this->action->authenticate($plain);

    Event::assertDispatched(UserAuthenticatedPasswordlessly::class, function ($event) {
        return $event->type === 'magic_link'
            && $event->authenticatable->id === $this->user->id;
    });
});

// -------------------------------------------------------------------------
// Full HTTP round-trip (uses the signed URL)
// -------------------------------------------------------------------------

it('logs the user in via a real signed URL', function () {
    $url = app(GenerateMagicLinkAction::class)->generate($this->user);

    $response = $this->get($url);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($this->user);
});

