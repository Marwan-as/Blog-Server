<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function getItemFromArrayById(array $array, int $id): ?array
    {
        $filtered = array_filter($array, function ($array) use ($id) {
            return $array['id'] === $id;
        });

        // Return the first matched user or null if not found
        return !empty($filtered) ? array_values($filtered)[0] : null;
    }
}
