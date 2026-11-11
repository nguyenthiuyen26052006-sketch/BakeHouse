<?php

declare(strict_types=1);

namespace App\Helpers;

final class Slugger
{
    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = str_replace(['đ', 'Đ'], 'd', $text);

        if (class_exists(\Transliterator::class)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($transliterator !== null) {
                $text = $transliterator->transliterate($text) ?: $text;
            }
        }

        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

        return trim($text, '-') ?: 'item';
    }
}
