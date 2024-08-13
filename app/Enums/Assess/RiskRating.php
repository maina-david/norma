<?php

namespace App\Enums\Assess;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static RiskRating notRated()
 * @method static RiskRating low()
 * @method static RiskRating medium()
 * @method static RiskRating high()
 */
class RiskRating extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'assess.assessment_item.risk_rating.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'notRated' => 0,
            'low' => 1,
            'medium' => 2,
            'high' => 3,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function colors(): array
    {
        return [
            static::notRated()->value => 'libryo-gray-300',
            static::low()->value => 'positive',
            static::medium()->value => 'warning',
            static::high()->value => 'negative',
        ];
    }
}
