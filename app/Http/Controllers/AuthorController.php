<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCoauthor;
use App\Models\User;
use App\Support\ImageUploadRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(Request $request, User $user)
    {
        $canSeeDrafts = $request->user()
            && ($request->user()->id === $user->id || $request->user()->isAdmin());

        $query = Article::with(['user', 'category', 'tags'])
            ->withRatingStats()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('coauthorRecords', function ($r) use ($user) {
                        $r->where('user_id', $user->id)
                            ->where('status', ArticleCoauthor::STATUS_ACCEPTED);
                    });
            });

        if (! $canSeeDrafts) {
            $query->published();
        }

        $articles = $query->latest()->paginate(12);
        $articles->through(function (Article $article) use ($user) {
            $article->is_coauthor = (int) $article->user_id !== (int) $user->id;

            return $article;
        });

        $isSubscribed = false;
        if ($request->user() && $request->user()->id !== $user->id) {
            $isSubscribed = $request->user()->isSubscribedTo($user);
        }

        $viewer = $request->user();
        $isOwnProfile = $viewer?->id === $user->id;

        return Inertia::render('Authors/Show', [
            'author' => $user->only(['id', 'name', 'email', 'bio', 'avatar', 'role', 'is_blocked', 'block_reason', 'blocked_until']),
            'articles' => $articles,
            'isSubscribed' => $isSubscribed,
            'isOwnProfile' => $isOwnProfile,
            'mustVerifyEmail' => $isOwnProfile && $viewer instanceof MustVerifyEmail,
            'status' => $isOwnProfile ? session('status') : null,
            'canManageRoles' => $viewer?->isOwner() && ! $isOwnProfile,
            'canManageUser' => $viewer && ! $isOwnProfile && $viewer->canManageUser($user),
            'articlesCount' => Article::where('user_id', $user->id)->count(),
            'subscribersCount' => $user->subscribers()->count(),
            'subscriptionsCount' => $user->subscriptions()->count(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'bio' => 'nullable|string|max:2000',
            ...ImageUploadRules::rule('avatar', 2048),
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $path = str_replace('/storage/', '', parse_url($user->avatar, PHP_URL_PATH) ?? '');
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            $user->avatar = Storage::url($request->file('avatar')->store('avatars', 'public'));
        }

        if ($request->has('bio')) {
            $user->bio = $request->bio;
        }

        $user->save();

        return back();
    }
}
