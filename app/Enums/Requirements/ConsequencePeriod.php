<?php

namespace App\Enums\Requirements;

use App\Enums\Enum;

/**
 * @method static ConsequencePeriod days()
 * @method static ConsequencePeriod weeks()
 * @method static ConsequencePeriod months()
 * @method static ConsequencePeriod years()
 * @method static ConsequencePeriod life()
 */
class ConsequencePeriod extends Enum
{
    protected static string $langPrefix = 'requirements.consequence.period_plain.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'days' => 1,
            'weeks' => 2,
            'months' => 3,
            'years' => 4,
            'life' => 5,
        ];
    }
}
