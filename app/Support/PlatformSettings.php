<?php

namespace App\Support;

use App\Models\Article;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class PlatformSettings
{
    private const CACHE_KEY = 'platform_settings.all';

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function hitRatingsThreshold(): int
    {
        return max(0, (int) self::get('hit_ratings_threshold', 10));
    }

    public static function hitRatingsDays(): int
    {
        return max(0, (int) self::get('hit_ratings_days', 7));
    }

    public static function evaluateHitStatus(Article $article): void
    {
        if ($article->is_hit) {
            return;
        }

        $threshold = self::hitRatingsThreshold();
        $days = self::hitRatingsDays();

        if ($threshold <= 0 || $days <= 0) {
            return;
        }

        $count = $article->votes()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        if ($count >= $threshold) {
            $article->forceFill(['is_hit' => true])->saveQuietly();
        }
    }

    private static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return PlatformSetting::query()
                ->pluck('value', 'key')
                ->all();
        });
    }
}
