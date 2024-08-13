<?php

namespace App\Enums\Metadata;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TagType legislation()
 * @method static TagType general()
 * @method static TagType indicator()
 * @method static TagType industry()
 */
class TagType extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'legislation' => 1,
            'general' => 2,
            'indicator' => 4,
            'industry' => 5,
        ];
    }
}
