<?php

namespace App\Enums\Tasks;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskPriority veryLow()
 * @method static TaskPriority low()
 * @method static TaskPriority medium()
 * @method static TaskPriority high()
 * @method static TaskPriority veryHigh()
 */
class TaskPriority extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'tasks.task.priority.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'veryLow' => 0,
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'veryHigh' => 4,
        ];
    }
}
