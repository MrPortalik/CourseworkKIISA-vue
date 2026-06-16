<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Notifications\ArticlePublicationApprovedNotification;
use App\Notifications\ArticlePublicationRejectedNotification;
use Illuminate\Http\Request;

class ArticleModerationController extends Controller
{
    public function approve(Request $request, Article $article)
    {
        abort_unless($request->user()->canModerateArticle($article), 403);

        if (! $article->is_publishable) {
            return back()->withErrors(['moderation' => 'Статья не отправлена на публикацию.']);
        }

        $wasPublished = $article->is_published;

        $article->update([
            'is_published' => true,
            'publication_rejection_reason' => null,
        ]);

        if (! $article->published_at || ! $wasPublished) {
            $article->published_at = now();
            $article->saveQuietly();
        }

        if (! $wasPublished) {
            $article->notifySubscribersOfPublication();
            $article->user?->notify(new ArticlePublicationApprovedNotification($article));
        }

        return back()->with('success', 'Статья опубликована.');
    }

    public function reject(Request $request, Article $article)
    {
        abort_unless($request->user()->canModerateArticle($article), 403);

        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $article->update([
            'is_publishable' => false,
            'is_published' => false,
            'publication_rejection_reason' => $request->reason,
            'published_at' => null,
        ]);

        $article->user?->notify(new ArticlePublicationRejectedNotification($article, $request->reason));

        return back()->with('success', 'Публикация отклонена, автор уведомлён.');
    }
}
