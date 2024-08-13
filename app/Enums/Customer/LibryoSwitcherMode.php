<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static LibryoSwitcherMode all()
 * @method static LibryoSwitcherMode single()
 */
class LibryoSwitcherMode extends Enum
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
