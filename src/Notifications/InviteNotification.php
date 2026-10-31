<?php

namespace crudPackage\Notifications;

use crudPackage\Mail\TemplateMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class InviteNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): TemplateMail
    {
        $url = url(route('invite.create', ['token' => $this->token], false)) . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        $mail = new TemplateMail('invite',
            [
                'name' => $notifiable->name,
                'days' => (int) round(Config::get('auth.passwords.invites.expire', 60) / 1440),
            ],
            $url,
        );

        return $mail->to($notifiable->getEmailForPasswordReset(), $notifiable->name);
    }
}
