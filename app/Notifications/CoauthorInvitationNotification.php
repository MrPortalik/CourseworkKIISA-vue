<?php

namespace App\Notifications;

use App\Models\Article;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CoauthorInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
        public User $inviter,
        public int $coauthorRecordId,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->article->loadMissing('user');

        return [
            'type' => 'coauthor_invitation',
            'coauthor_id' => $this->coauthorRecordId,
            'article_id' => $this->article->id,
            'article_slug' => $this->article->slug,
            'article_title' => $this->article->title,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'message' => $this->inviter->name.' приглашает вас стать со-автором статьи «'.$this->article->title.'»',
        ];
    }
}
