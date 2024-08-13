<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static SystemNotificationAlertType alert()
 * @method static SystemNotificationAlertType modal()
 */
class SystemNotificationAlertType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'system.system_notification.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'alert' => 1,
            'modal' => 2,
        ];
    }
}
