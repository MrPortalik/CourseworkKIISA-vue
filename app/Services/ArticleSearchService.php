<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ArticleSearchService
{
    public function rankArticles(Collection $articles, string $rawQuery): array
    {
        $parsed = $this->parseQuery($rawQuery);
        if ($parsed['titleQuery'] === '' && $parsed['authorQuery'] === null) {
            return ['exact' => collect(), 'related' => collect()];
        }

        $scored = $articles->map(function ($article) use ($parsed) {
            $title = mb_strtolower($article->title);
            $authorName = mb_strtolower($article->user->name ?? '');

            $score = 0;
            $exact = false;

            if ($parsed['authorQuery'] !== null && $parsed['authorQuery'] !== '') {
                if ($authorName === $parsed['authorQuery']) {
                    $score = 1000;
                    $exact = true;
                } elseif ($parsed['authorQuery'] !== '' && mb_strpos($authorName, $parsed['authorQuery']) === 0) {
                    $score = 900;
                    $exact = true;
                } elseif ($parsed['authorQuery'] !== '' && str_contains($authorName, $parsed['authorQuery'])) {
                    $score = 400;
                }
            }

            if ($parsed['titleQuery'] !== '') {
                $titleScore = $this->scoreTitle($title, $parsed['titleQuery']);
                if ($titleScore > $score) {
                    $score = $titleScore;
                }
                if ($this->isExactTitleMatch($title, $parsed['titleQuery'])) {
                    $exact = true;
                }
            }

            return ['article' => $article, 'score' => $score, 'exact' => $exact];
        })->filter(fn ($row) => $row['score'] > 0)
            ->sortByDesc('score')
            ->values();

        return [
            'exact' => $scored->where('exact', true)->pluck('article')->values(),
            'related' => $scored->where('exact', false)->pluck('article')->values(),
        ];
    }

    public function parseQuery(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/^автор\s*:\s*(.*)$/iu', $raw, $m)) {
            return [
                'authorQuery' => mb_strtolower(trim($m[1])),
                'titleQuery' => '',
            ];
        }

        return [
            'authorQuery' => null,
            'titleQuery' => mb_strtolower($raw),
        ];
    }

    public function isExactTitleMatch(string $title, string $query): bool
    {
        if ($query === '') {
            return false;
        }

        if ($title === $query) {
            return true;
        }

        return mb_strpos($title, $query) === 0;
    }

    public function scoreTitle(string $title, string $query): int
    {
        if ($this->isExactTitleMatch($title, $query)) {
            return 1000;
        }

        if (str_contains($title, $query)) {
            return 500 - (mb_strlen($title) - mb_strlen($query));
        }

        similar_text($title, $query, $percent);
        if ($percent < 35) {
            return 0;
        }

        $lev = levenshtein(
            Str::ascii(mb_substr($title, 0, 255)),
            Str::ascii(mb_substr($query, 0, 255))
        );

        return max(0, (int) $percent - $lev);
    }

    public function splitForListing(string $query): array
    {
        $published = Article::published()
            ->with(['user', 'category', 'tags'])
            ->get();

        $ranked = $this->rankArticles($published, $query);

        return [
            'exact' => $ranked['exact'],
            'related' => $ranked['related'],
            'hasExact' => $ranked['exact']->isNotEmpty(),
        ];
    }
}
