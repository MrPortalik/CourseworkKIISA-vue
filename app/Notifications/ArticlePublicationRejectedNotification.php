<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArticlePublicationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
        public string $reason,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'publication_rejected',
            'article_id' => $this->article->id,
            'article_slug' => $this->article->slug,
            'article_title' => $this->article->title,
            'reason' => $this->reason,
            'message' => 'Публикация статьи «'.$this->article->title.'» отклонена: '.$this->reason,
        ];
    }
}
