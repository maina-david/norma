<?php

namespace App\Enums\Requirements;

use App\Enums\Enum;

/**
 * @method static ConsequenceType fine()
 * @method static ConsequenceType prison()
 * @method static ConsequenceType other()
 */
class ConsequenceType extends Enum
{
    protected static string $langPrefix = 'requirements.consequence.type.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            //            'offence' => 1,
            //            'administrative' => 2,
            'fine' => 3,
            'prison' => 4,
            'other' => 10,
        ];
    }
}
