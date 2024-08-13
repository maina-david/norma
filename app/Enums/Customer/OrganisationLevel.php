<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static OrganisationLevel a()
 * @method static OrganisationLevel b()
 * @method static OrganisationLevel c()
 * @method static OrganisationLevel d()
 * @method static OrganisationLevel e()
 * @method static OrganisationLevel f()
 * @method static OrganisationLevel g()
 */
class OrganisationLevel extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'customer.organisation.organisation_level.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,string>
     */
    protected static function enums(): array
    {
        return [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
            'g' => 'G',
        ];
    }
}
