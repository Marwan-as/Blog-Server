<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }


    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
