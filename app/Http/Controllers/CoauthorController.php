<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCoauthor;
use App\Models\User;
use App\Services\CoauthorInvitationService;
use Illuminate\Http\Request;

class CoauthorController extends Controller
{
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->q);
        if ($q === '') {
            return response()->json(['users' => []]);
        }

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where(function ($query) use ($q) {
                if (ctype_digit($q)) {
                    $query->where('id', (int) $q);
                }
                $query->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
            })
            ->limit(15)
            ->get(['id', 'name', 'email']);

        return response()->json(['users' => $users]);
    }

    public function invite(Request $request, Article $article)
    {
        $this->authorizeArticle($request, $article);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $inviteeId = (int) $request->user_id;
        if ($inviteeId === $article->user_id) {
            return back()->withErrors(['coauthor' => 'Автор статьи уже указан как основной.']);
        }

        if ($inviteeId === $request->user()->id && $request->user()->id !== $article->user_id) {
            return back()->withErrors(['coauthor' => 'Нельзя пригласить себя.']);
        }

        app(CoauthorInvitationService::class)->invite($article, $inviteeId, $request->user());

        return back()->with('success', 'Приглашение отправлено.');
    }

    public function accept(Request $request, ArticleCoauthor $coauthor)
    {
        if ($coauthor->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($coauthor->status !== ArticleCoauthor::STATUS_PENDING) {
            return back();
        }

        $coauthor->update(['status' => ArticleCoauthor::STATUS_ACCEPTED]);

        return back()->with('success', 'Вы приняли приглашение стать со-автором.');
    }

    public function decline(Request $request, ArticleCoauthor $coauthor)
    {
        if ($coauthor->user_id !== $request->user()->id) {
            abort(403);
        }

        $coauthor->update(['status' => ArticleCoauthor::STATUS_DECLINED]);

        return back()->with('success', 'Приглашение отклонено.');
    }

    private function authorizeArticle(Request $request, Article $article): void
    {
        if ($request->user()->id !== $article->user_id && ! $request->user()->isAdmin()) {
            abort(403);
        }
    }
}
