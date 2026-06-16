<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AccountBlockedNotification;
use Carbon\Carbon;

class UserBlocking
{
    public const SPAM_REASON = 'Автоматическая блокировка за спам: слишком много отправленных статей за короткий период.';

    public static function refreshStatus(User $user): User
    {
        if ($user->is_blocked && $user->blocked_until && $user->blocked_until->isPast()) {
            $user->update([
                'is_blocked' => false,
                'block_reason' => null,
                'blocked_until' => null,
            ]);
            $user->refresh();
        }

        return $user;
    }

    public static function isActive(User $user): bool
    {
        $user = self::refreshStatus($user);

        return (bool) $user->is_blocked;
    }

    public static function block(
        User $user,
        string $reason,
        ?Carbon $until = null,
        bool $notify = true,
        bool $isSpam = false,
    ): void {
        $permanent = $until === null;

        $user->update([
            'is_blocked' => true,
            'block_reason' => $reason,
            'blocked_until' => $until,
        ]);

        if ($notify) {
            $user->notify(new AccountBlockedNotification(
                $reason,
                $until?->toIso8601String(),
                $permanent,
                $isSpam,
            ));
        }
    }

    public static function unblock(User $user): void
    {
        $user->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_until' => null,
        ]);
    }

    /**
     * @return array{reason: string, permanent: bool, blocked_until: ?string}
     */
    public static function info(User $user): array
    {
        $user = self::refreshStatus($user);

        if (! $user->is_blocked) {
            return [
                'reason' => '',
                'permanent' => false,
                'blocked_until' => null,
            ];
        }

        return [
            'reason' => $user->block_reason ?: 'Доступ к аккаунту ограничен администрацией.',
            'permanent' => $user->blocked_until === null,
            'blocked_until' => $user->blocked_until?->toIso8601String(),
        ];
    }
}
