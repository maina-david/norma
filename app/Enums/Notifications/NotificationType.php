<?php

namespace App\Enums\Notifications;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static NotificationType mention()
 * @method static NotificationType reply()
 * @method static NotificationType reminder()
 * @method static NotificationType taskAssigned()
 * @method static NotificationType taskAssigneeChanged()
 * @method static NotificationType taskDueDateChanged()
 * @method static NotificationType taskStatusChanged()
 * @method static NotificationType taskDue()
 * @method static NotificationType taskPriorityChanged()
 * @method static NotificationType taskCompleted()
 * @method static NotificationType taskTitleChanged()
 */
class NotificationType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'notifications.types.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'mention' => 1,
            'reply' => 2,
            'reminder' => 3,
            'taskAssigned' => 4,
            'taskAssigneeChanged' => 5,
            'taskDueDateChanged' => 6,
            'taskStatusChanged' => 7,
            'taskDue' => 8,
            'taskPriorityChanged' => 9,
            'taskCompleted' => 10,
            'taskTitleChanged' => 11,
        ];
    }
}
