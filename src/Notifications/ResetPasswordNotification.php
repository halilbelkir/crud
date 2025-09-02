<?php

namespace crudPackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Şifre Sıfırlama Talebi')
            ->line('Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:')
            ->view('crudPackage::emails.customResetPassword',
                [
                    'name_surname' => '',
                    'actionUrl'    => url(route('password.reset', $this->token, false))
                ])
            ->line('Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayın.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
