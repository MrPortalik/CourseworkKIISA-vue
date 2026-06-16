<?php

namespace App\Notifications;

use App\Models\Article;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArticleEditedNotification extends Notification
{
    use Queueable;

    public function __construct(public Article $article, public User $editor)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'article_edited',
            'article_id' => $this->article->id,
            'article_slug' => $this->article->slug,
            'article_title' => $this->article->title,
            'editor_id' => $this->editor->id,
            'editor_name' => $this->editor->name,
            'message' => $this->editor->name.' отредактировал(а) статью «'.$this->article->title.'»',
        ];
    }
}
