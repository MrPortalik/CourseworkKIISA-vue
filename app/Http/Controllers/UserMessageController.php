<?php

namespace App\Http\Controllers;

use App\Models\UserMessage;
use App\Notifications\AdminMessageNotification;
use App\Support\NotificationState;
use Illuminate\Http\Request;

class UserMessageController extends Controller
{
    public function reply(Request $request, UserMessage $message)
    {
        abort_unless($message->recipient_id === $request->user()->id, 403);
        abort_if($message->parent_id !== null, 422);

        $validated = $request->validate([
            'body' => 'required|string|min:5|max:5000',
        ]);

        $reply = UserMessage::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $message->sender_id,
            'parent_id' => $message->id,
            'body' => $validated['body'],
        ]);

        $reply->load('sender');
        $message->sender?->notify(new AdminMessageNotification($reply));

        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        $request->user()
            ->notifications()
            ->where('data->message_id', $message->id)
            ->where('data->type', 'admin_message')
            ->get()
            ->each(function ($notification) {
                NotificationState::setAction($notification, 'replied');
                $notification->markAsRead();
            });

        return back()->with('status', 'Ответ отправлен.');
    }
}
