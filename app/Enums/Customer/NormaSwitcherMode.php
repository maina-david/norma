<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static NormaSwitcherMode all()
 * @method static NormaSwitcherMode single()
 */
class NormaSwitcherMode extends Enum
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
