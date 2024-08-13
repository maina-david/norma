<?php

namespace App\Enums\Corpus;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ReferenceStatus pending()
 * @method static ReferenceStatus active()
 */
class ReferenceStatus extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'pending' => 1,
            'active' => 2,
        ];
    }
}
