<?php

namespace App\Enums\Requirements;

use App\Enums\Enum;

/**
 * @method static PenaltyType fine()
 * @method static PenaltyType prison()
 * @method static PenaltyType costs()
 * @method static PenaltyType community()
 * @method static PenaltyType other()
 */
class PenaltyType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'fine' => 1,
            'prison' => 2,
            'costs' => 3,
            'community' => 4,
            'other' => 10,
        ];
    }
}
