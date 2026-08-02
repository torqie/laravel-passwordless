<?php

use Illuminate\Support\Facades\Notification;
use Torqie\LaravelPasswordless\Notifications\LoginCodeNotification;
use Torqie\LaravelPasswordless\Notifications\MagicLinkNotification;
use Torqie\LaravelPasswordless\Support\SignedUrlBuilder;
use Torqie\LaravelPasswordless\Tests\Fakes\FakeAuthenticateViaLoginCodeAction;
use Torqie\LaravelPasswordless\Tests\Fakes\FakeAuthenticateViaMagicLinkAction;
use Torqie\LaravelPasswordless\Tests\Fakes\FakeGenerateLoginCodeAction;
use Torqie\LaravelPasswordless\Tests\Fakes\FakeGenerateMagicLinkAction;
use Torqie\LaravelPasswordless\Tests\Fakes\FakeResolveUserForSendAction;
use Torqie\LaravelPasswordless\Tests\Fakes\RegisteringResolveUserForSendAction;
use Torqie\LaravelPasswordless\Tests\Models\User;

/*
|--------------------------------------------------------------------------
| config('passwordless.actions.*') must take effect through the HTTP flow
|--------------------------------------------------------------------------
| Every test here swaps an implementation via config only — no container
| bindings — and drives it through a real request against the package routes.
*/

beforeEach(function () {
    FakeGenerateMagicLinkAction::reset();
    FakeAuthenticateViaMagicLinkAction::reset();
    FakeGenerateLoginCodeAction::reset();
    FakeAuthenticateViaLoginCodeAction::reset();
    FakeResolveUserForSendAction::reset();
    RegisteringResolveUserForSendAction::reset();

    $this->user = User::create(['email' => 'swap@example.com']);
});

// -------------------------------------------------------------------------
// generate_magic_link
// -------------------------------------------------------------------------

it('uses the configured generate_magic_link action on POST /auth/magic-link', function () {
    Notification::fake();
    config()->set('passwordless.actions.generate_magic_link', FakeGenerateMagicLinkAction::class);

    $this->post(route('passwordless.magic-link.send'), ['email' => $this->user->email])
        ->assertOk();

    expect(FakeGenerateMagicLinkAction::$called)->toBeTrue();
    expect(FakeGenerateMagicLinkAction::$receivedUser->id)->toBe($this->user->id);

    Notification::assertSentTo(
        $this->user,
        fn (MagicLinkNotification $notification) => $notification->magicLinkUrl === FakeGenerateMagicLinkAction::URL
    );
});

// -------------------------------------------------------------------------
// authenticate_magic_link
// -------------------------------------------------------------------------

it('uses the configured authenticate_magic_link action on GET /auth/magic-link/{token}', function () {
    config()->set('passwordless.actions.authenticate_magic_link', FakeAuthenticateViaMagicLinkAction::class);

    $url = app(SignedUrlBuilder::class)->buildMagicLinkUrl('some-plain-token', 15);

    $this->get($url)
        ->assertRedirect(FakeAuthenticateViaMagicLinkAction::REDIRECT);

    expect(FakeAuthenticateViaMagicLinkAction::$called)->toBeTrue();
    expect(FakeAuthenticateViaMagicLinkAction::$receivedToken)->toBe('some-plain-token');
});

// -------------------------------------------------------------------------
// generate_login_code
// -------------------------------------------------------------------------

it('uses the configured generate_login_code action on POST /auth/code', function () {
    Notification::fake();
    config()->set('passwordless.actions.generate_login_code', FakeGenerateLoginCodeAction::class);

    $this->post(route('passwordless.login-code.send'), ['email' => $this->user->email])
        ->assertRedirect(route('passwordless.login-code.verify'));

    expect(FakeGenerateLoginCodeAction::$called)->toBeTrue();
    expect(FakeGenerateLoginCodeAction::$receivedUser->id)->toBe($this->user->id);

    Notification::assertSentTo(
        $this->user,
        fn (LoginCodeNotification $notification) => $notification->code === FakeGenerateLoginCodeAction::CODE
    );
});

// -------------------------------------------------------------------------
// authenticate_login_code
// -------------------------------------------------------------------------

it('uses the configured authenticate_login_code action on POST /auth/code/verify', function () {
    config()->set('passwordless.actions.authenticate_login_code', FakeAuthenticateViaLoginCodeAction::class);

    $this->withSession(['passwordless.pending_email' => $this->user->email])
        ->post(route('passwordless.login-code.authenticate'), ['code' => '123456'])
        ->assertRedirect(FakeAuthenticateViaLoginCodeAction::REDIRECT);

    expect(FakeAuthenticateViaLoginCodeAction::$called)->toBeTrue();
    expect(FakeAuthenticateViaLoginCodeAction::$receivedEmail)->toBe($this->user->email);
    expect(FakeAuthenticateViaLoginCodeAction::$receivedCode)->toBe('123456');
});

// -------------------------------------------------------------------------
// resolve_user
// -------------------------------------------------------------------------

it('uses the configured resolve_user action on POST /auth/magic-link', function () {
    Notification::fake();
    config()->set('passwordless.actions.resolve_user', FakeResolveUserForSendAction::class);

    $recipient = User::create(['email' => 'redirected@example.com']);
    FakeResolveUserForSendAction::$returns = $recipient;

    $this->post(route('passwordless.magic-link.send'), ['email' => 'anything@example.com'])
        ->assertOk();

    expect(FakeResolveUserForSendAction::$called)->toBeTrue();
    expect(FakeResolveUserForSendAction::$receivedEmail)->toBe('anything@example.com');
    expect(FakeResolveUserForSendAction::$receivedIp)->not->toBeEmpty();

    Notification::assertSentTo($recipient, MagicLinkNotification::class);
    Notification::assertNotSentTo($this->user, MagicLinkNotification::class);
});

it('uses the configured resolve_user action on POST /auth/code', function () {
    Notification::fake();
    config()->set('passwordless.actions.resolve_user', FakeResolveUserForSendAction::class);

    $recipient = User::create(['email' => 'redirected@example.com']);
    FakeResolveUserForSendAction::$returns = $recipient;

    $this->post(route('passwordless.login-code.send'), ['email' => 'anything@example.com'])
        ->assertRedirect(route('passwordless.login-code.verify'));

    expect(FakeResolveUserForSendAction::$called)->toBeTrue();
    expect(FakeResolveUserForSendAction::$receivedEmail)->toBe('anything@example.com');

    Notification::assertSentTo($recipient, LoginCodeNotification::class);
});

it('honours a null return from the configured resolve_user action', function () {
    Notification::fake();
    config()->set('passwordless.actions.resolve_user', FakeResolveUserForSendAction::class);

    FakeResolveUserForSendAction::$returns = null;

    $this->post(route('passwordless.magic-link.send'), ['email' => $this->user->email])
        ->assertOk()
        ->assertViewIs('laravel-passwordless::magic-link.sent');

    Notification::assertNothingSent();
});

// -------------------------------------------------------------------------
// resolve_user — passwordless sign-up
// -------------------------------------------------------------------------

it('lets a resolve_user subclass register an unknown email instead of staying silent', function () {
    Notification::fake();
    config()->set('passwordless.actions.resolve_user', RegisteringResolveUserForSendAction::class);

    $this->post(route('passwordless.magic-link.send'), ['email' => 'newcomer@example.com'])
        ->assertOk();

    expect(RegisteringResolveUserForSendAction::$created)->toBe(1);

    $created = User::where('email', 'newcomer@example.com')->firstOrFail();

    Notification::assertSentTo($created, MagicLinkNotification::class);
});

it('keeps the send rate limit working through a resolve_user subclass calling parent::handle()', function () {
    Notification::fake();
    config()->set('passwordless.actions.resolve_user', RegisteringResolveUserForSendAction::class);
    config()->set('passwordless.rate_limits.send', 2);

    $this->post(route('passwordless.magic-link.send'), ['email' => 'newcomer@example.com']);
    $this->post(route('passwordless.magic-link.send'), ['email' => 'newcomer@example.com']);

    $this->post(route('passwordless.magic-link.send'), ['email' => 'newcomer@example.com'])
        ->assertSessionHasErrors('email');

    // The third request was throttled before the subclass could register anyone.
    expect(RegisteringResolveUserForSendAction::$created)->toBe(1);
});
