<?php

namespace App\Enums\Requirements;

use App\Enums\Enum;

/**
 * @method static ConsequenceAmountType currency()
 * @method static ConsequenceAmountType percentageRevenue()
 * @method static ConsequenceAmountType penaltyUnits()
 * @method static ConsequenceAmountType penaltyFines()
 * @method static ConsequenceAmountType currencyPoints()
 * @method static ConsequenceAmountType penaltyPoints()
 * @method static ConsequenceAmountType standardScale()
 */
class ConsequenceAmountType extends Enum
{
    protected static string $langPrefix = 'requirements.consequence.amount_type.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'currency' => 1,
            'percentageRevenue' => 2,
            'penaltyUnits' => 3, // Australia and various African countries
            'penaltyFines' => 4, // Australia
            'currencyPoints' => 5, // Uganda
            'penaltyPoints' => 6, // Ireland
            'standardScale' => 7, // UK
        ];
    }
}
