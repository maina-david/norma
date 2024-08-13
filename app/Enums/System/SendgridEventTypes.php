<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property string $value
 *
 * @method static self processed()
 * @method static self dropped()
 * @method static self delivered()
 * @method static self deferred()
 * @method static self bounce()
 * @method static self open()
 * @method static self spamreport()
 */
class SendgridEventTypes extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string>
     */
    protected static function enums(): array
    {
        return [
            'processed',
            'dropped',
            'delivered',
            'deferred',
            'bounce',
            'open',
            'spamreport',
        ];
    }
}
