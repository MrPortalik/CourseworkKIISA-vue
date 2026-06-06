<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PlatformReport;
use Illuminate\Http\Request;

class PlatformReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:article_complaint,feedback',
            'message' => 'required|string|min:10|max:5000',
            'article_id' => 'nullable|exists:articles,id',
        ]);

        if ($validated['type'] === PlatformReport::TYPE_ARTICLE_COMPLAINT) {
            $article = Article::findOrFail($validated['article_id']);
            if (! $article->is_published) {
                abort(422, 'Нельзя пожаловаться на неопубликованную статью.');
            }
        } else {
            $validated['article_id'] = null;
        }

        PlatformReport::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'article_id' => $validated['article_id'],
            'message' => $validated['message'],
            'status' => PlatformReport::STATUS_PENDING,
        ]);

        return back()->with('status', 'Сообщение отправлено. Спасибо за обратную связь!');
    }
}
