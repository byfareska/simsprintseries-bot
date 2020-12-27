<?php declare(strict_types=1);

namespace App\Helper;

use stdClass;

final class ArrayHelper
{
    public static function sortByObjectProperty(array $array, string $property): array
    {
        usort($array, fn(stdClass $a, stdClass $b) => self::compare($a->$property, $b->$property));
        return $array;
    }

    private static function compare($a, $b): int
    {
        return $a === $b ? 0 : ($a < $b ? -1 : 1);
    }
}