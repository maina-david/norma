<?php

namespace App\Enums\Workflows;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskStatus pending()
 * @method static TaskStatus todo()
 * @method static TaskStatus inProgress()
 * @method static TaskStatus inReview()
 * @method static TaskStatus done()
 * @method static TaskStatus archive()
 */
class TaskStatus extends Enum
{
    protected static string $langPrefix = 'workflows.task.task_status.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'pending' => 0,
            'todo' => 1,
            'inProgress' => 2,
            'inReview' => 3,
            'done' => 4,
            'archive' => 5,
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
            self::done()->value, self::archive()->value => 'bg-red-800',
            self::inReview()->value => 'bg-red-600',
            self::inProgress()->value => 'bg-red-400',
            default => 'bg-red-200',
        };
    }
}
