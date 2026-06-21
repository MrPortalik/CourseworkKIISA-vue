<?php

namespace App\Support;

use App\Models\Article;
use App\Models\ArticleCoauthor;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationState
{
    public static function enrich(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        if (($data['type'] ?? null) === 'coauthor_invitation' && empty($data['user_action'])) {
            $coauthor = ArticleCoauthor::find($data['coauthor_id'] ?? null);

            if ($coauthor?->status === ArticleCoauthor::STATUS_ACCEPTED) {
                $data['user_action'] = 'accepted';
            } elseif ($coauthor?->status === ArticleCoauthor::STATUS_DECLINED) {
                $data['user_action'] = 'declined';
            }
        }

        if ($notification->read_at && empty($data['user_action'])) {
            $data['user_action'] = 'read';
        }

        if (! empty($data['article_slug'])) {
            $article = Article::with('user')->where('slug', $data['article_slug'])->first();

            $data['article_available'] = $article !== null && ! ($article->user?->is_blocked ?? false);
        }

        if (! empty($data['author_id'])) {
            $data['author_available'] = User::where('id', $data['author_id'])->exists();
        }

        return $data;
    }

    public static function setAction(DatabaseNotification $notification, string $action): void
    {
        $data = $notification->data;
        $data['user_action'] = $action;
        $notification->forceFill(['data' => $data])->save();
    }

    public static function label(string $action): string
    {
        return match ($action) {
            'accepted' => 'Вы подтвердили со-авторство',
            'declined' => 'Вы отклонили приглашение',
            'read' => 'Вы прочли уведомление',
            'replied' => 'Вы ответили на сообщение',
            'opened_article' => 'Вы открыли статью',
            default => 'Действие выполнено',
        };
    }
}
