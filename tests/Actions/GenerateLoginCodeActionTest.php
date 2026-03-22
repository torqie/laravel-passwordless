<?php

use Torqie\LaravelPasswordless\Actions\GenerateLoginCodeAction;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user   = User::create(['email' => 'test@example.com']);
    $this->action = app(GenerateLoginCodeAction::class);
});

it('creates a login_code token record in the database', function () {
    $this->action->generate($this->user);

    expect(PasswordlessToken::count())->toBe(1);

    $token = PasswordlessToken::first();
    expect($token->type)->toBe('login_code');
    expect($token->used_at)->toBeNull();
});

it('returns the plain-text code directly', function () {
    $code = $this->action->generate($this->user);

    expect($code)->toBeString()->not->toBeEmpty();
});

it('stores a SHA-256 hash of the code, not the plain text', function () {
    $code  = $this->action->generate($this->user);
    $token = PasswordlessToken::first();

    expect($token->token)->toBe(hash('sha256', $code));
    expect($token->token)->not->toBe($code);
});

it('generates a code with the configured length', function () {
    config()->set('passwordless.code.length', 8);

    $code = $this->action->generate($this->user);

    expect(strlen($code))->toBe(8);
});

it('generates a code using only characters from the configured charset', function () {
    config()->set('passwordless.code.charset', 'ABCDEF');
    config()->set('passwordless.code.length', 10);

    $code = $this->action->generate($this->user);

    expect($code)->toMatch('/^[ABCDEF]{10}$/');
});

it('sets expires_at according to the configured TTL', function () {
    config()->set('passwordless.ttl', 5);

    $this->action->generate($this->user);

    $token   = PasswordlessToken::first();
    $minutes = abs($token->expires_at->diffInMinutes(now()));
    expect($minutes)->toBeGreaterThanOrEqual(4)->toBeLessThanOrEqual(6);
});

it('associates the token with the correct authenticatable', function () {
    $this->action->generate($this->user);

    $token = PasswordlessToken::first();
    expect($token->authenticatable_type)->toBe(User::class);
    expect($token->authenticatable_id)->toBe($this->user->id);
});

it('throws an InvalidArgumentException for an empty charset', function () {
    config()->set('passwordless.code.charset', '');

    expect(fn () => $this->action->generate($this->user))
        ->toThrow(\InvalidArgumentException::class);
});

