<?php

namespace App\Http\Controllers;

use App\Support\NotificationState;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->through(function ($notification) {
                $data = NotificationState::enrich($notification);

                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'action_label' => ! empty($data['user_action'])
                        ? NotificationState::label($data['user_action'])
                        : null,
                ];
            });

        $authors = $request->user()
            ->subscribedAuthors()
            ->withCount(['articles as published_articles_count' => fn ($q) => $q->published()])
            ->get();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'authors' => $authors,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();

        if (empty($notification->data['user_action'])) {
            NotificationState::setAction($notification, 'read');
        }

        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->each(function ($notification) {
            if (empty($notification->data['user_action'])) {
                NotificationState::setAction($notification, 'read');
            }
            $notification->markAsRead();
        });

        return back();
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->where('id', $id)->delete();

        return back();
    }
}
