<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCoauthor;
use App\Models\User;
use App\Notifications\CoauthorInvitationNotification;

class CoauthorInvitationService
{
    public function invite(Article $article, int $inviteeId, User $inviter): void
    {
        if ($inviteeId === $article->user_id || $inviteeId === $inviter->id) {
            return;
        }

        $existing = ArticleCoauthor::where('article_id', $article->id)
            ->where('user_id', $inviteeId)
            ->first();

        if ($existing) {
            if (in_array($existing->status, [ArticleCoauthor::STATUS_ACCEPTED, ArticleCoauthor::STATUS_PENDING], true)) {
                return;
            }
        }

        $record = ArticleCoauthor::updateOrCreate(
            [
                'article_id' => $article->id,
                'user_id' => $inviteeId,
            ],
            [
                'invited_by' => $inviter->id,
                'status' => ArticleCoauthor::STATUS_PENDING,
            ]
        );

        $invitee = User::find($inviteeId);
        if ($invitee) {
            $invitee->notify(new CoauthorInvitationNotification($article, $inviter, $record->id));
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    public function inviteMany(Article $article, array $userIds, User $inviter): void
    {
        foreach (array_unique(array_map('intval', $userIds)) as $userId) {
            if ($userId > 0) {
                $this->invite($article, $userId, $inviter);
            }
        }
    }
}
