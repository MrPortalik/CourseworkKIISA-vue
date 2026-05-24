<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriberMilestoneNotification extends Notification
{
    use Queueable;

    public function __construct(public int $subscriberCount)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $formatted = number_format($this->subscriberCount, 0, '', ' ');

        return [
            'type' => 'subscriber_milestone',
            'author_id' => $notifiable->id,
            'subscriber_count' => $this->subscriberCount,
            'message' => "Поздравляем! У вас {$formatted} подписчиков.",
        ];
    }
}
