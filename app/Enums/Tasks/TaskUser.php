<?php

namespace App\Enums\Tasks;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskUser assignee()
 * @method static TaskUser creator()
 * @method static TaskUser followers()
 */
class TaskUser extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'tasks.task.task_user.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'assignee' => 1,
            'creator' => 2,
            'followers' => 3,
        ];
    }
}
