<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canViewAllDrafts = $user->isStaff();

        $pending = Article::with(['user', 'category'])
            ->where('is_publishable', true)
            ->where('is_published', false)
            ->latest()
            ->paginate(12, ['*'], 'pending_page')
            ->withQueryString();

        $allDrafts = $canViewAllDrafts
            ? Article::with(['user', 'category'])
                ->where('is_published', false)
                ->latest()
                ->paginate(12, ['*'], 'drafts_page')
                ->withQueryString()
            : null;

        return Inertia::render('Admin/Index', [
            'pendingArticles' => $pending,
            'allDrafts' => $allDrafts,
            'canViewAllDrafts' => $canViewAllDrafts,
        ]);
    }
}
