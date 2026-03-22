<?php

use Illuminate\Support\Facades\Notification;
use Wiredrhino\LaravelPasswordless\Notifications\MagicLinkNotification;
use Wiredrhino\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'notify@example.com']);
});

it('is sent via the mail channel', function () {
    Notification::fake();

    $this->user->notify(new MagicLinkNotification('https://example.com/magic'));

    Notification::assertSentTo($this->user, MagicLinkNotification::class, function ($n) {
        return in_array('mail', $n->via($this->user));
    });
});

it('toMail returns a MailMessage with the correct subject', function () {
    $notification = new MagicLinkNotification('https://example.com/magic');
    $mail         = $notification->toMail($this->user);

    expect($mail->subject)->toBe('Your Magic Login Link');
});

it('toMail uses the configured custom view when set', function () {
    config()->set('passwordless.views.magic_link_email', 'emails.custom-magic');

    $notification = new MagicLinkNotification('https://example.com/magic');
    $mail         = $notification->toMail($this->user);

    expect($mail->view)->toBe('emails.custom-magic');
});

it('toMail defaults to the package email view', function () {
    $notification = new MagicLinkNotification('https://example.com/magic');
    $mail         = $notification->toMail($this->user);

    expect($mail->view)->toBe('laravel-passwordless::emails.magic-link');
});

it('exposes the URL in the view data', function () {
    $url          = 'https://example.com/magic-abc';
    $notification = new MagicLinkNotification($url);
    $mail         = $notification->toMail($this->user);

    expect($mail->viewData['url'])->toBe($url);
});

