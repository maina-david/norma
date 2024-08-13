<?php

namespace App\Enums\Workflows;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskPriority immediate()
 * @method static TaskPriority urgent()
 * @method static TaskPriority high()
 * @method static TaskPriority medium()
 * @method static TaskPriority low()
 */
class TaskPriority extends Enum
{
    protected static string $langPrefix = 'workflows.task.task_priority.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'immediate' => 0,
            'urgent' => 1,
            'high' => 2,
            'medium' => 3,
            'low' => 4,
        ];
    }

    /**
     * Get the colour of the task status.
     *
     * @return string
     */
    public function colour(): string
    {
        return match ($this->value) {
            self::immediate()->value => 'bg-red-800',
            self::urgent()->value => 'bg-red-600',
            self::high()->value => 'bg-red-400',
            self::medium()->value => 'bg-red-200 text-libryo-gray-600',
            default => 'bg-red-100 text-libryo-gray-600',
        };
    }
}
