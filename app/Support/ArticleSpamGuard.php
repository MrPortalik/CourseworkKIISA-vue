<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ArticleSpamGuard
{
    private const MAX_SUBMISSIONS = 4;

    private const WINDOW_MINUTES = 5;

    public function shouldBlockForSubmission(User $user): bool
    {
        if ($user->canModerateArticles()) {
            return false;
        }

        $key = "article_submissions:{$user->id}";
        $timestamps = Cache::get($key, []);
        $cutoff = now()->subMinutes(self::WINDOW_MINUTES)->timestamp;
        $timestamps = array_values(array_filter($timestamps, fn (int $ts) => $ts >= $cutoff));

        if (count($timestamps) >= self::MAX_SUBMISSIONS) {
            return true;
        }

        $timestamps[] = now()->timestamp;
        Cache::put($key, $timestamps, now()->addMinutes(self::WINDOW_MINUTES + 1));

        return false;
    }

    public function blockForSpam(User $user): void
    {
        UserBlocking::block(
            $user,
            UserBlocking::SPAM_REASON,
            Carbon::now()->addDay(),
            notify: true,
            isSpam: true,
        );
    }
}
