<?php

namespace App\Support;

class ObjectNumberParser
{
    public static function fromTitle(string $title): ?int
    {
        if (preg_match('/объект\s*№?\s*(\d+)/iu', $title, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/объект\s+(\d+)/iu', $title, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public static function hundredRangeLabel(int $hundredIndex): string
    {
        if ($hundredIndex === 0) {
            return '1–99';
        }

        $from = $hundredIndex * 100;
        $to = ($hundredIndex + 1) * 100 - 1;

        return $from.'–'.$to;
    }
}
