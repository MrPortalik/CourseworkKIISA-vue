<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArticlePublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public Article $article)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->article->loadMissing('user');

        return [
            'article_id' => $this->article->id,
            'article_slug' => $this->article->slug,
            'article_title' => $this->article->title,
            'author_id' => $this->article->user_id,
            'author_name' => $this->article->user->name,
            'message' => $this->article->user->name.' опубликовал статью «'.$this->article->title.'»',
        ];
    }
}
