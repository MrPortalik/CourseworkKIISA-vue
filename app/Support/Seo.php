<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    public static function articleDescription(?string $content): string
    {
        $text = preg_replace('/\[изображение:[^\]]+\]/iu', '', (string) $content);
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return Str::limit($text, 160, '…');
    }
}
