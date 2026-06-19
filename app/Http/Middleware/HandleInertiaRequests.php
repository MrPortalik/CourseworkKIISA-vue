<?php

namespace App\Http\Middleware;

use App\Models\Article;
use App\Models\Category;
use App\Models\PlatformReport;
use App\Support\ObjectNumberParser;
use App\Support\UserEmailHash;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $maxObject = \App\Models\Article::whereNotNull('object_number')->max('object_number') ?? 0;
        $maxRange = (int) floor(max($maxObject, 99) / 100);

        $objectRanges = [];
        for ($i = 0; $i <= $maxRange; $i++) {
            $objectRanges[] = [
                'index' => $i,
                'label' => ObjectNumberParser::hundredRangeLabel($i),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? $this->serializeAuthUser($user) : null,
            ],
            'unreadNotificationsCount' => $user
                ? $user->unreadNotifications()->count()
                : 0,
            'sidebarCategories' => Category::orderBy('name')->get(['id', 'name']),
            'sidebarTags' => \App\Models\Tag::orderBy('name')->get(['id', 'name', 'slug']),
            'objectRanges' => $objectRanges,
            'pendingReportsCount' => $user && $user->isStaff()
                ? PlatformReport::where('status', PlatformReport::STATUS_PENDING)->count()
                : 0,
            'pendingModerationCount' => $user && $user->canAccessAdminPanel()
                ? Article::where('is_publishable', true)->where('is_published', false)->count()
                : 0,
        ];
    }

    private function serializeAuthUser($user): array
    {
        $data = $user->toArray();
        $data['email'] = $user->getPlainEmail() ?? '';

        return $data;
    }
}
