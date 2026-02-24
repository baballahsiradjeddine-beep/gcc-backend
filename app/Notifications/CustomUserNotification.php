<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomUserNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $body;
    public ?string $imageUrl;

    public function __construct(string $title, string $body, ?string $imageUrl = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->imageUrl = $imageUrl;
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
            'image' => $this->imageUrl,
        ];
    }
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->imageUrl,
        ];
    }
}
