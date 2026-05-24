<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleVote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'vote' => 'required|in:1,-1',
        ]);

        $vote = (int) $request->vote;

        ArticleVote::updateOrCreate(
            ['article_id' => $article->id, 'user_id' => $request->user()->id],
            ['vote' => $vote]
        );

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
