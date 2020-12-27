<?php declare(strict_types=1);

namespace App\Helper;

final class TextHelper
{
    public static function normalize(string $string): string
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string));
    }
}