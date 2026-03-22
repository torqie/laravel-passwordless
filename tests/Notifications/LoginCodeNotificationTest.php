<?php

use Illuminate\Support\Facades\Notification;
use Torqie\LaravelPasswordless\Notifications\LoginCodeNotification;
use Torqie\LaravelPasswordless\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'codeme@example.com']);
});

it('is sent via the mail channel', function () {
    Notification::fake();

    $this->user->notify(new LoginCodeNotification('123456'));

    Notification::assertSentTo($this->user, LoginCodeNotification::class, function ($n) {
        return in_array('mail', $n->via($this->user));
    });
});

it('toMail returns a MailMessage with the correct subject', function () {
    $notification = new LoginCodeNotification('123456');
    $mail = $notification->toMail($this->user);

    expect($mail->subject)->toBe('Your Login Code');
});

it('toMail uses the configured custom view when set', function () {
    config()->set('passwordless.views.login_code_email', 'emails.custom-code');

    $notification = new LoginCodeNotification('123456');
    $mail = $notification->toMail($this->user);

    expect($mail->view)->toBe('emails.custom-code');
});

it('toMail defaults to the package email view', function () {
    $notification = new LoginCodeNotification('123456');
    $mail = $notification->toMail($this->user);

    expect($mail->view)->toBe('laravel-passwordless::emails.login-code');
});

it('exposes the code in the view data', function () {
    $notification = new LoginCodeNotification('987654');
    $mail = $notification->toMail($this->user);

    expect($mail->viewData['code'])->toBe('987654');
});
