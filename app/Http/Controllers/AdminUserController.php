<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Models\UserMessage;
use App\Notifications\AdminMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('id', $q);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => ['q' => $request->q],
            'canManageRoles' => $request->user()->isOwner(),
        ]);
    }

    public function show(Request $request, User $user)
    {
        $articlesCount = Article::where('user_id', $user->id)->count();
        $messages = UserMessage::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->whereNull('parent_id')
            ->with(['sender', 'recipient', 'replies.sender'])
            ->latest()
            ->limit(30)
            ->get();

        return Inertia::render('Admin/UserShow', [
            'profileUser' => $user->only(['id', 'name', 'email', 'bio', 'avatar', 'role', 'is_blocked', 'created_at']),
            'articlesCount' => $articlesCount,
            'messages' => $messages,
            'canManageRoles' => $request->user()->isOwner(),
            'canManageUser' => $request->user()->canManageUser($user),
        ]);
    }

    public function promoteAdmin(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($user->isOwner(), 422);
        abort_if($user->isAdmin(), 422);

        $user->update(['role' => 'admin']);

        return back();
    }

    public function demoteAdmin(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($user->isOwner(), 422);
        abort_unless($user->role === 'admin', 422);

        $user->update(['role' => 'user']);

        return back();
    }

    public function block(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403);
        abort_if($user->isAdmin() && ! $request->user()->isOwner(), 403);

        $user->update(['is_blocked' => true]);

        return back();
    }

    public function unblock(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403);

        $user->update(['is_blocked' => false]);

        return back();
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($user->isOwner(), 422);
        abort_if($request->user()->id === $user->id, 422);

        $user->delete();

        return redirect()->route('admin.users.index');
    }

    public function sendMessage(Request $request, User $user)
    {
        abort_unless($request->user()->isStaff(), 403);

        $validated = $request->validate([
            'body' => 'required|string|min:5|max:5000',
        ]);

        $message = UserMessage::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $message->load('sender');
        $user->notify(new AdminMessageNotification($message));

        return back()->with('status', 'Сообщение отправлено.');
    }
}
