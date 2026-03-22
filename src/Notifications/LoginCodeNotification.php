<?php

namespace Wiredrhino\LaravelPasswordless\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $view = config('passwordless.views.login_code_email') ?? 'laravel-passwordless::emails.login-code';

        return (new MailMessage)
            ->subject(__('Your Login Code'))
            ->view($view, [
                'code' => $this->code,
                'expiresMins' => config('passwordless.ttl', 15),
            ]);
    }
}
