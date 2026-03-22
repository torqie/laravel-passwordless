<?php

use Illuminate\Support\Facades\URL;
use Torqie\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user   = User::create(['email' => 'test@example.com']);
    $this->action = app(GenerateMagicLinkAction::class);
});

it('creates a magic_link token record in the database', function () {
    $this->action->generate($this->user);

    expect(PasswordlessToken::count())->toBe(1);

    $token = PasswordlessToken::first();
    expect($token->type)->toBe('magic_link');
    expect($token->used_at)->toBeNull();
    expect($token->plain_text)->toBeNull();
});

it('sets expires_at according to the configured TTL', function () {
    config()->set('passwordless.ttl', 30);

    $this->action->generate($this->user);

    $token   = PasswordlessToken::first();
    $minutes = abs($token->expires_at->diffInMinutes(now()));
    expect($minutes)->toBeGreaterThanOrEqual(29)->toBeLessThanOrEqual(31);
});

it('stores a SHA-256 hash, not the plain token', function () {
    $url = $this->action->generate($this->user);

    // token is a *path* parameter: http://localhost/auth/magic-link/{TOKEN}?expires=...
    $path       = (string) parse_url($url, PHP_URL_PATH);
    $plainToken = basename($path);

    $dbToken = PasswordlessToken::first();
    expect($dbToken->token)->toBe(hash('sha256', $plainToken));
    expect($dbToken->token)->not->toBe($plainToken);
});

it('returns a signed URL pointing to the authenticate route', function () {
    $url = $this->action->generate($this->user);

    expect($url)->toContain('/auth/magic-link/');
    expect($url)->toContain('signature=');
    expect($url)->toContain('expires=');
});

it('returns a URL that passes Laravel signature verification', function () {
    $url = $this->action->generate($this->user);

    expect(URL::hasValidSignature(request()->create($url)))->toBeTrue();
});

it('associates the token with the correct authenticatable', function () {
    $this->action->generate($this->user);

    $token = PasswordlessToken::first();
    expect($token->authenticatable_type)->toBe(User::class);
    expect($token->authenticatable_id)->toBe($this->user->id);
});

