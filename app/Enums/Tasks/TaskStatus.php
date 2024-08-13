<?php

namespace App\Enums\Tasks;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskStatus notStarted()
 * @method static TaskStatus inProgress()
 * @method static TaskStatus done()
 * @method static TaskStatus paused()
 */
class TaskStatus extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'tasks.task.status.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'notStarted' => 0,
            'inProgress' => 1,
            'done' => 2,
            'paused' => 3,
        ];
    }
}
