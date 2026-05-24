<?php

namespace App\Http\Controllers;

use App\Models\AuthorSubscription;
use App\Models\User;
use App\Notifications\SubscriberMilestoneNotification;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscriptionsPage(Request $request)
    {
        $authors = $request->user()
            ->subscribedAuthors()
            ->withCount(['articles as published_articles_count' => fn ($q) => $q->published()])
            ->get();

        return \Inertia\Inertia::render('Subscriptions/Index', [
            'authors' => $authors,
        ]);
    }

    public function store(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['subscription' => 'Нельзя подписаться на себя.']);
        }

        $subscription = AuthorSubscription::firstOrCreate([
            'follower_id' => $request->user()->id,
            'author_id' => $user->id,
        ]);

        if ($subscription->wasRecentlyCreated) {
            $this->notifySubscriberMilestoneIfNeeded($user);
        }

        return back();
    }

    private function notifySubscriberMilestoneIfNeeded(User $author): void
    {
        $count = AuthorSubscription::where('author_id', $author->id)->count();
        $milestones = [1, 100, 1000, 100000, 1000000, 10000000];

        if (in_array($count, $milestones, true)) {
            $author->notify(new SubscriberMilestoneNotification($count));
        }
    }

    public function destroy(Request $request, User $user)
    {
        AuthorSubscription::where('follower_id', $request->user()->id)
            ->where('author_id', $user->id)
            ->delete();

        return back();
    }
}
