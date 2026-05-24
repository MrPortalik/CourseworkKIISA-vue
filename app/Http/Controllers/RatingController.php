<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleVote;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Article $article)
    {
        if ($article->user_id === $request->user()->id) {
            abort(403, 'Нельзя оценивать собственную статью.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating = (int) $request->rating;
        $existing = ArticleVote::where('article_id', $article->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && (int) $existing->vote === $rating) {
            $existing->delete();
        } else {
            ArticleVote::updateOrCreate(
                ['article_id' => $article->id, 'user_id' => $request->user()->id],
                ['vote' => $rating]
            );
        }

        return back();
    }

    public function destroy(Request $request, Article $article)
    {
        ArticleVote::where('article_id', $article->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back();
    }
}
