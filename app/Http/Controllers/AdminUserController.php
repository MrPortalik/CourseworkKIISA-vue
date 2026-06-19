<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Models\UserMessage;
use App\Notifications\AdminMessageNotification;
use App\Support\UserBlocking;
use App\Support\UserEmailHash;
use Carbon\Carbon;
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
                        ->orWhere('id', $q);

                    if (filter_var($q, FILTER_VALIDATE_EMAIL)) {
                        $inner->orWhere('email', User::hashEmail($q));
                    }
                });
            })
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function (User $user) {
                $data = $user->toArray();
                $data['email'] = UserEmailHash::displayLabel($user->getAttributes()['email'] ?? null);

                return $data;
            });

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
            'profileUser' => array_merge(
                $user->only(['id', 'name', 'bio', 'avatar', 'role', 'is_blocked', 'block_reason', 'blocked_until', 'created_at']),
                ['email' => UserEmailHash::displayLabel($user->getAttributes()['email'] ?? null)],
            ),
            'articlesCount' => $articlesCount,
            'messages' => $messages,
            'canManageRoles' => $request->user()->isOwner(),
            'canPromoteModerator' => $request->user()->isOwner() || $request->user()->isAdmin(),
            'canManageUser' => $request->user()->canManageUser($user),
        ]);
    }

    public function promoteAdmin(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($user->isOwner(), 422);
        abort_if($user->isAdmin() || $user->isModerator(), 422);

        $user->update(['role' => 'admin']);

        return back();
    }

    public function promoteModerator(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner() || $request->user()->isAdmin(), 403);
        abort_if($user->isOwner(), 422);
        abort_if($user->isAdmin() || $user->isModerator(), 422);

        $user->update(['role' => 'moderator']);

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

    public function demoteModerator(Request $request, User $user)
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($user->isOwner(), 422);
        abort_unless($user->role === 'moderator', 422);

        $user->update(['role' => 'user']);

        return back();
    }

    public function block(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403);
        abort_if(($user->isAdmin() || $user->isModerator()) && ! $request->user()->isOwner(), 403);

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:2000',
            'is_permanent' => 'boolean',
            'duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        $until = null;
        if (! $request->boolean('is_permanent')) {
            $days = $validated['duration_days'] ?? 1;
            $until = Carbon::now()->addDays($days);
        }

        UserBlocking::block($user, $validated['reason'], $until);

        return back();
    }

    public function unblock(Request $request, User $user)
    {
        abort_unless($request->user()->canManageUser($user), 403);

        UserBlocking::unblock($user);

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
