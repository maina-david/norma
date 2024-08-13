<?php

namespace App\Enums\Tasks;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskActivityType statusChange()
 * @method static TaskActivityType madeComment()
 * @method static TaskActivityType documentUpload()
 * @method static TaskActivityType assigneeChange()
 * @method static TaskActivityType watchTask()
 * @method static TaskActivityType dueDateChanged()
 * @method static TaskActivityType setReminder()
 * @method static TaskActivityType priorityChanged()
 * @method static TaskActivityType titleChanged()
 */
class TaskActivityType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'statusChange' => 1,
            'madeComment' => 2,
            'documentUpload' => 3,
            'assigneeChange' => 4,
            'watchTask' => 5,
            'dueDateChanged' => 6,
            'setReminder' => 7,
            'priorityChanged' => 8,
            'titleChanged' => 9,
        ];
    }
}
