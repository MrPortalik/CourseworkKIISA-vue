<?php

namespace App\Notifications;

use App\Models\UserMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public UserMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin_message',
            'message_id' => $this->message->id,
            'parent_id' => $this->message->parent_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name,
            'message' => $this->message->body,
        ];
    }
}
