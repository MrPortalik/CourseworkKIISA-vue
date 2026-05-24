<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(Request $request, User $user)
    {
        $query = Article::with(['user', 'category', 'tags'])->where('user_id', $user->id);

        $canSeeDrafts = $request->user()
            && ($request->user()->id === $user->id || $request->user()->isAdmin());

        if (! $canSeeDrafts) {
            $query->published();
        }

        $articles = $query->latest()->paginate(12);

        $isSubscribed = false;
        if ($request->user() && $request->user()->id !== $user->id) {
            $isSubscribed = $request->user()->isSubscribedTo($user);
        }

        return Inertia::render('Authors/Show', [
            'author' => $user->only(['id', 'name', 'email', 'bio', 'avatar']),
            'articles' => $articles,
            'isSubscribed' => $isSubscribed,
            'isOwnProfile' => $request->user()?->id === $user->id,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'bio' => 'nullable|string|max:2000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
