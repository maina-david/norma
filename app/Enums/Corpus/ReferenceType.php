<?php

namespace App\Enums\Corpus;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ReferenceType bearer()
 * @method static ReferenceType obligation()
 * @method static ReferenceType prohibition()
 * @method static ReferenceType exception()
 * @method static ReferenceType right()
 * @method static ReferenceType consequenceGroup()
 * @method static ReferenceType technical()
 * @method static ReferenceType procedural()
 * @method static ReferenceType constitutive()
 * @method static ReferenceType applicability()
 * @method static ReferenceType citation()
 * @method static ReferenceType authorityPower()
 * @method static ReferenceType authorityObligation()
 * @method static ReferenceType explanatory()
 * @method static ReferenceType deeming()
 * @method static ReferenceType work()
 * @method static ReferenceType amendment()
 * @method static ReferenceType process()
 */
class ReferenceType extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'bearer' => 1,
            'obligation' => 2,
            'prohibition' => 3,
            'exception' => 4,
            'right' => 5,
            'consequenceGroup' => 6,
            'technical' => 7,
            'procedural' => 8,
            'constitutive' => 9,
            'applicability' => 10,
            'citation' => 11,
            'authorityPower' => 12,
            'authorityObligation' => 13,
            'explanatory' => 14,
            'deeming' => 15,
            'work' => 16,
            'amendment' => 17,
            'process' => 18,
        ];
    }

    /**
     * Get the types that can be parents of others.
     *
     * @codeCoverageIgnore
     *
     * @return array<array-key, int>
     */
    public static function allowedParents(): array
    {
        return [
            self::citation()->value,
            self::work()->value,
        ];
    }
}
