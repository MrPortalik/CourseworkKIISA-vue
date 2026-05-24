<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($request->parent_id) {
            $parent = Comment::where('article_id', $article->id)->findOrFail($request->parent_id);
        }

        Comment::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        return back();
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($request->user()->id !== $comment->user_id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $comment->delete();

        return back();
    }
}
