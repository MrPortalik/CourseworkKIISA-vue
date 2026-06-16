<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountBlockedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $reason,
        public ?string $blockedUntilIso = null,
        public bool $permanent = false,
        public bool $isSpam = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $prefix = $this->isSpam
            ? 'Предупреждение: ваш аккаунт заблокирован за спам.'
            : 'Ваш аккаунт заблокирован.';

        return [
            'type' => 'account_blocked',
            'reason' => $this->reason,
            'blocked_until' => $this->blockedUntilIso,
            'permanent' => $this->permanent,
            'is_spam' => $this->isSpam,
            'message' => $prefix.' '.$this->reason,
        ];
    }
}
