<?php

use Torqie\LaravelPasswordless\Models\PasswordlessToken;
use Torqie\LaravelPasswordless\Tests\Models\User;

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

function makeUser(string $email = 'test@example.com'): User
{
    return User::create(['email' => $email]);
}

function makeToken(User $user, array $overrides = []): PasswordlessToken
{
    return PasswordlessToken::create(array_merge([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $user->id,
        'token'                => hash('sha256', uniqid('token', true)),
        'type'                 => 'magic_link',
        'plain_text'           => null,
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => null,
    ], $overrides));
}

// -------------------------------------------------------------------------
// isExpired / isUsed / isValid
// -------------------------------------------------------------------------

it('isExpired returns true when expires_at is past', function () {
    $token = makeToken(makeUser(), ['expires_at' => now()->subSecond()]);
    expect($token->isExpired())->toBeTrue();
});

it('isExpired returns false when expires_at is future', function () {
    $token = makeToken(makeUser());
    expect($token->isExpired())->toBeFalse();
});

it('isUsed returns true when used_at is set', function () {
    $token = makeToken(makeUser(), ['used_at' => now()]);
    expect($token->isUsed())->toBeTrue();
});

it('isUsed returns false when used_at is null', function () {
    $token = makeToken(makeUser());
    expect($token->isUsed())->toBeFalse();
});

it('isValid returns true when not expired and not used', function () {
    $token = makeToken(makeUser());
    expect($token->isValid())->toBeTrue();
});

it('isValid returns false for expired token', function () {
    $token = makeToken(makeUser(), ['expires_at' => now()->subSecond()]);
    expect($token->isValid())->toBeFalse();
});

it('isValid returns false for used token', function () {
    $token = makeToken(makeUser(), ['used_at' => now()]);
    expect($token->isValid())->toBeFalse();
});

it('markUsed sets used_at to now', function () {
    $token = makeToken(makeUser());
    expect($token->used_at)->toBeNull();

    $token->markUsed();

    expect($token->fresh()->used_at)->not->toBeNull();
});

// -------------------------------------------------------------------------
// Scopes
// -------------------------------------------------------------------------

it('valid() scope returns only non-expired, non-used tokens', function () {
    $user = makeUser();
    makeToken($user);                                                              // valid
    makeToken($user, ['expires_at' => now()->subSecond()]);                        // expired
    makeToken($user, ['used_at' => now()]);                                        // used
    makeToken($user, ['expires_at' => now()->subSecond(), 'used_at' => now()]);   // both

    expect(PasswordlessToken::valid()->count())->toBe(1);
});

it('unused() scope excludes used tokens', function () {
    $user = makeUser();
    makeToken($user);
    makeToken($user, ['used_at' => now()]);

    expect(PasswordlessToken::unused()->count())->toBe(1);
});

it('ofType() scope filters by type', function () {
    $user = makeUser();
    makeToken($user, ['type' => 'magic_link']);
    makeToken($user, ['type' => 'login_code']);

    expect(PasswordlessToken::ofType('magic_link')->count())->toBe(1);
    expect(PasswordlessToken::ofType('login_code')->count())->toBe(1);
});

// -------------------------------------------------------------------------
// Relationships
// -------------------------------------------------------------------------

it('authenticatable() morphs back to the owning model', function () {
    $user  = makeUser();
    $token = makeToken($user);

    expect($token->authenticatable)->toBeInstanceOf(User::class);
    expect($token->authenticatable->id)->toBe($user->id);
});

// -------------------------------------------------------------------------
// HasPasswordlessAuth trait
// -------------------------------------------------------------------------

it('passwordlessTokens() returns all tokens for the user', function () {
    $user  = makeUser();
    $other = makeUser('other@example.com');

    makeToken($user);
    makeToken($user);
    makeToken($other);

    expect($user->passwordlessTokens()->count())->toBe(2);
});

it('validPasswordlessTokens() returns only valid tokens', function () {
    $user = makeUser();
    makeToken($user);
    makeToken($user, ['expires_at' => now()->subSecond()]);
    makeToken($user, ['used_at' => now()]);

    expect($user->validPasswordlessTokens()->count())->toBe(1);
});

it('invalidatePasswordlessTokens() marks all unused tokens as used', function () {
    $user = makeUser();
    makeToken($user);
    makeToken($user);
    makeToken($user, ['used_at' => now()]);  // already used

    $user->invalidatePasswordlessTokens();

    expect(PasswordlessToken::unused()->count())->toBe(0);
});

it('invalidatePasswordlessTokens() can scope to a single type', function () {
    $user = makeUser();
    makeToken($user, ['type' => 'magic_link']);
    makeToken($user, ['type' => 'login_code']);

    $user->invalidatePasswordlessTokens('magic_link');

    expect(PasswordlessToken::ofType('magic_link')->unused()->count())->toBe(0);
    expect(PasswordlessToken::ofType('login_code')->unused()->count())->toBe(1);
});

