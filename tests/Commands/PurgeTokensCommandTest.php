<?php

use Illuminate\Support\Facades\Artisan;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;
use Wiredrhino\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $user = User::create(['email' => 'purge@example.com']);

    // 3 tokens: one valid, one expired, one used-but-not-expired
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $user->id,
        'token'                => hash('sha256', 'valid'),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => null,
    ]);
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $user->id,
        'token'                => hash('sha256', 'expired'),
        'type'                 => 'magic_link',
        'expires_at'           => now()->subMinute(),
        'used_at'              => null,
    ]);
    PasswordlessToken::create([
        'authenticatable_type' => User::class,
        'authenticatable_id'   => $user->id,
        'token'                => hash('sha256', 'used'),
        'type'                 => 'magic_link',
        'expires_at'           => now()->addMinutes(15),
        'used_at'              => now()->subSecond(),
    ]);
});

it('purges both expired and used tokens by default', function () {
    Artisan::call('passwordless:purge');

    expect(PasswordlessToken::count())->toBe(1);
    expect(PasswordlessToken::first()->token)->toBe(hash('sha256', 'valid'));
});

it('purges only expired tokens with --expired flag', function () {
    Artisan::call('passwordless:purge', ['--expired' => true]);

    expect(PasswordlessToken::count())->toBe(2); // valid + used remain
    expect(PasswordlessToken::where('expires_at', '<', now())->count())->toBe(0);
});

it('purges only used tokens with --used flag', function () {
    Artisan::call('passwordless:purge', ['--used' => true]);

    expect(PasswordlessToken::count())->toBe(2); // valid + expired remain
    expect(PasswordlessToken::whereNotNull('used_at')->count())->toBe(0);
});

it('outputs the number of purged tokens', function () {
    Artisan::call('passwordless:purge');
    $output = Artisan::output();

    expect($output)->toContain('Purged 2 passwordless token(s).');
});

