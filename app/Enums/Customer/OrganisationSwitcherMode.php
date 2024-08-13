<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static OrganisationSwitcherMode all()
 * @method static OrganisationSwitcherMode single()
 */
class OrganisationSwitcherMode extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array<int,string>
     */
    protected static function enums(): array
    {
        return [
            'all',
            'single',
        ];
    }
}
