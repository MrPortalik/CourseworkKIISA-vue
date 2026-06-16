<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PlatformReport;
use App\Models\User;
use Illuminate\Http\Request;

class PlatformReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:article_complaint,user_complaint,feedback,site_complaint',
            'message' => 'required|string|min:10|max:5000',
            'article_id' => 'nullable|exists:articles,id',
            'reported_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['type'] === PlatformReport::TYPE_ARTICLE_COMPLAINT) {
            $article = Article::findOrFail($validated['article_id']);
            if (! $article->is_published) {
                abort(422, 'Нельзя пожаловаться на неопубликованную статью.');
            }
            $validated['reported_user_id'] = null;
        } elseif ($validated['type'] === PlatformReport::TYPE_USER_COMPLAINT) {
            $reportedUser = User::findOrFail($validated['reported_user_id']);
            if ($reportedUser->id === $request->user()->id) {
                abort(422, 'Нельзя пожаловаться на себя.');
            }
            $validated['article_id'] = null;
        } else {
            $validated['article_id'] = null;
            $validated['reported_user_id'] = null;
        }

        PlatformReport::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'article_id' => $validated['article_id'],
            'reported_user_id' => $validated['reported_user_id'],
            'message' => $validated['message'],
            'status' => PlatformReport::STATUS_PENDING,
        ]);

        return back()->with('status', 'Сообщение отправлено. Спасибо за обратную связь!');
    }
}
