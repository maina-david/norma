<?php

namespace App\Enums\Traits;

trait EnumToValues
{
    /**
     * @return array<string|int>
     */
    public static function options(): array
    {
        return array_map(fn ($e) => $e->value, static::cases());
    }

    /**
     * @return string
     */
    public static function implode(string $glue = ','): string
    {
        return implode($glue, static::options());
    }
}
