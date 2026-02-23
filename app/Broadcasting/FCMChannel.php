<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use App\Services\FCMService;

class FCMChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toFcm')) {
            $message = $notification->toFcm($notifiable);

            if (!empty($message['token'])) {
                FCMService::send($message['token'], $message['title'], $message['body'], $message['data'] ?? []);
            }
        }
    }
}
