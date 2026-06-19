<?php

namespace App\Support;

use App\Models\Article;

class ExplanatoryNoteParser
{
    public static function fromTitle(string $title): ?int
    {
        if (preg_match('/п[зz]\s*№?\s*(\d+)/iu', $title, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/пояснительн\w*\s+записк\w*\s*№?\s*(\d+)/iu', $title, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public static function isExplanatoryNote(Article $article): bool
    {
        if (self::fromTitle($article->title) !== null) {
            return true;
        }

        $article->loadMissing(['category', 'categories']);

        if ($article->category && self::isExplanatoryCategoryName($article->category->name)) {
            return true;
        }

        foreach ($article->categories as $category) {
            if (self::isExplanatoryCategoryName($category->name)) {
                return true;
            }
        }

        return false;
    }

    public static function isExplanatoryCategoryName(string $name): bool
    {
        return mb_stripos($name, 'пояснительн') !== false;
    }
}
