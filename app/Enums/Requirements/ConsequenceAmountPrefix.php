<?php

namespace App\Enums\Requirements;

use App\Enums\Enum;

/**
 * @method static ConsequenceAmountPrefix upTo()
 * @method static ConsequenceAmountPrefix exactly()
 * @method static ConsequenceAmountPrefix atLeast()
 */
class ConsequenceAmountPrefix extends Enum
{
    protected static string $langPrefix = 'requirements.consequence.amount_prefix_of.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'upTo' => 1,
            'exactly' => 2,
            'atLeast' => 3,
        ];
    }
}
