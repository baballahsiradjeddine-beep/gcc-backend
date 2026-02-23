<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $body;

    public function __construct(string $title, string $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->fcm_token)) {
            $channels[] = \App\Broadcasting\FCMChannel::class;
        }
        return $channels;
    }

    public function toFcm($notifiable)
    {
        return [
            'token' => $notifiable->fcm_token,
            'title' => $this->title,
            'body'  => $this->body,
        ];
    }
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
