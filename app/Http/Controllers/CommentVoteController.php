<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\Request;

class CommentVoteController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        if ($comment->user_id === $request->user()->id) {
            abort(403, 'Нельзя оценивать собственный комментарий.');
        }

        $request->validate(['vote' => 'required|in:1,-1']);

        $vote = (int) $request->vote;
        $existing = CommentVote::where('comment_id', $comment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && (int) $existing->vote === $vote) {
            $existing->delete();
        } else {
            CommentVote::updateOrCreate(
                ['comment_id' => $comment->id, 'user_id' => $request->user()->id],
                ['vote' => $vote]
            );
        }

        return back();
    }
}
