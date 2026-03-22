<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction;
use Torqie\LaravelPasswordless\Events\UserAuthenticatedPasswordlessly;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'test@example.com']);
    $this->action = app(AuthenticateViaLoginCodeAction::class);
    RateLimiter::clear('passwordless:code:test@example.com');
});

// -------------------------------------------------------------------------
// Invalid code paths
// -------------------------------------------------------------------------

it('throws a ValidationException for a wrong code', function () {
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', '123456'),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    expect(fn () => $this->action->authenticate('test@example.com', '000000'))
        ->toThrow(ValidationException::class);
});

it('throws a ValidationException for an expired code', function () {
    $plain = '999999';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->subMinute(),
        'used_at' => null,
    ]);

    expect(fn () => $this->action->authenticate('test@example.com', $plain))
        ->toThrow(ValidationException::class);
});

it('throws a ValidationException for an unknown email', function () {
    expect(fn () => $this->action->authenticate('nobody@example.com', '123456'))
        ->toThrow(ValidationException::class);
});

// -------------------------------------------------------------------------
// Rate limiting
// -------------------------------------------------------------------------

it('increments the rate limit counter on each failure', function () {
    expect(RateLimiter::attempts('passwordless:code:test@example.com'))->toBe(0);

    try {
        $this->action->authenticate('test@example.com', 'wrong');
    } catch (ValidationException) {
    }

    expect(RateLimiter::attempts('passwordless:code:test@example.com'))->toBe(1);
});

it('throws with a rate-limit message after too many attempts', function () {
    // Exhaust all attempts
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit('passwordless:code:test@example.com');
    }

    expect(fn () => $this->action->authenticate('test@example.com', '123456'))
        ->toThrow(ValidationException::class, 'Too many failed attempts');
});

// -------------------------------------------------------------------------
// Success path
// -------------------------------------------------------------------------

it('redirects to after_login on a valid code', function () {
    $plain = '246810';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $response = $this->action->authenticate('test@example.com', $plain);

    expect($response->getTargetUrl())->toContain('/dashboard');
});

it('marks the token as used after a successful authentication', function () {
    $plain = '135790';
    $token = PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $this->action->authenticate('test@example.com', $plain);

    expect($token->fresh()->used_at)->not->toBeNull();
});

it('clears the rate limit counter on success', function () {
    RateLimiter::hit('passwordless:code:test@example.com');

    $plain = '112233';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $this->action->authenticate('test@example.com', $plain);

    expect(RateLimiter::attempts('passwordless:code:test@example.com'))->toBe(0);
});

it('fires the UserAuthenticatedPasswordlessly event with type login_code', function () {
    Event::fake();

    $plain = '334455';
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $this->user->id,
        'token' => hash('sha256', $plain),
        'type' => 'login_code',
        'expires_at' => now()->addMinutes(15),
        'used_at' => null,
    ]);

    $this->action->authenticate('test@example.com', $plain);

    Event::assertDispatched(UserAuthenticatedPasswordlessly::class, function ($event) {
        return $event->type === 'login_code'
            && $event->authenticatable->id === $this->user->id;
    });
});
