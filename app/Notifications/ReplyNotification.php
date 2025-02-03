<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Reply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReplyNotification extends Notification
{
    use Queueable;
    private $reply;
    private $replier;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reply $reply, User $replier)
    {
        $this->reply = $reply;
        $this->replier = $replier;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->replier->name} replied to your comment.",
            'post_id' => $this->reply->post_id,
            'comment_id' => $this->reply->id,
            'commenter_id' => $this->replier->id,
            'commenter_name' => $this->replier->name,
            'commenter_image' => $this->replier->profileImagePath,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
