<?php

namespace Torqie\LaravelPasswordless\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $magicLinkUrl
    ) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $view = config('passwordless.views.magic_link_email') ?? 'laravel-passwordless::emails.magic-link';

        return (new MailMessage)
            ->subject(__('Your Magic Login Link'))
            ->view($view, [
                'url'         => $this->magicLinkUrl,
                'expiresMins' => config('passwordless.ttl', 15),
            ]);
    }
}

