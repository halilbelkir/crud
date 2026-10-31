<?php

namespace crudPackage\Notifications;

use crudPackage\Mail\TemplateMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification
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
        $url = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        $mail = new TemplateMail('password_reset',
            [
                'name'   => $notifiable->name,
                'expire' => (int) Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire', 60),
            ],
            $url,
        );

        return $mail->to($notifiable->getEmailForPasswordReset(), $notifiable->name);
    }
}
