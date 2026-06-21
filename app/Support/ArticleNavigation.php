<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class ArticleNavigation
{
    /** @var array<int, string>|null slug list keyed by order */
    private static ?array $orderedSlugs = null;

    public static function nextSlug(Article $article): ?string
    {
        return self::adjacentSlug($article, 1);
    }

    public static function previousSlug(Article $article): ?string
    {
        return self::adjacentSlug($article, -1);
    }

    public static function randomSlug(?Article $exclude = null): ?string
    {
        $query = Article::published();

        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        return $query->inRandomOrder()->value('slug');
    }

    public static function nextDraftSlug(Article $article, User $viewer): ?string
    {
        return self::draftAdjacentSlug($article, $viewer, 1);
    }

    public static function previousDraftSlug(Article $article, User $viewer): ?string
    {
        return self::draftAdjacentSlug($article, $viewer, -1);
    }

    private static function draftAdjacentSlug(Article $article, User $viewer, int $direction): ?string
    {
        if ($article->is_published) {
            return null;
        }

        $query = Article::unpublished()->latest();

        if ($viewer->isAdmin()) {
            // all drafts (admin default scope)
        } elseif ($viewer->canModerateArticles() && $article->user_id !== $viewer->id) {
            $query->publishable();
        } else {
            $query->where('user_id', $article->user_id);
        }

        $slugs = $query->pluck('slug')->all();
        $index = array_search($article->slug, $slugs, true);

        if ($index === false) {
            return null;
        }

        $target = $index + $direction;

        if ($target < 0 || $target >= count($slugs)) {
            return null;
        }

        return $slugs[$target];
    }

    private static function adjacentSlug(Article $article, int $direction): ?string
    {
        if (! $article->is_published) {
            return null;
        }

        $slugs = self::orderedSlugs();
        $index = array_search($article->slug, $slugs, true);

        if ($index === false) {
            return null;
        }

        $target = $index + $direction;

        if ($target < 0 || $target >= count($slugs)) {
            return null;
        }

        return $slugs[$target];
    }

  /**
   * Published articles in global order: categories A→Z, within each category titles A→Z, no duplicates.
   *
   * @return array<int, string>
   */
    private static function orderedSlugs(): array
    {
        if (self::$orderedSlugs !== null) {
            return self::$orderedSlugs;
        }

        $seen = [];
        $slugs = [];

        $categories = Category::orderBy('name')->get();

        foreach ($categories as $category) {
            $articles = Article::published()
                ->where(function ($query) use ($category) {
                    $query->where('category_id', $category->id)
                        ->orWhereHas('categories', fn ($c) => $c->where('categories.id', $category->id));
                })
                ->orderBy('title')
                ->get(['id', 'slug']);

            foreach ($articles as $article) {
                if (isset($seen[$article->id])) {
                    continue;
                }

                $seen[$article->id] = true;
                $slugs[] = $article->slug;
            }
        }

        if ($seen !== []) {
            $remaining = Article::published()
                ->whereNotIn('id', array_keys($seen))
                ->orderBy('title')
                ->get(['id', 'slug']);
        } else {
            $remaining = Article::published()->orderBy('title')->get(['id', 'slug']);
        }

        foreach ($remaining as $article) {
            if (isset($seen[$article->id])) {
                continue;
            }

            $seen[$article->id] = true;
            $slugs[] = $article->slug;
        }

        self::$orderedSlugs = $slugs;

        return self::$orderedSlugs;
    }
}
