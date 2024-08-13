<?php

namespace App\Observers\Workflows;

use App\Models\Workflows\Task;
use App\Models\Workflows\TaskCheck;
use App\Notifications\Workflows\TaskCheckedNotification;

class TaskCheckObserver
{
    /**
     * Listen to the created event.
     *
     * @param TaskCheck $check
     *
     * @return void
     */
    public function created(TaskCheck $check): void
    {
        $check->load(['task.dependedBy.assignee']);

        $check->task->dependedBy->each(function ($task) {
            /** @var Task $task */
            if ($task->assignee) {
                $task->assignee->notify(new TaskCheckedNotification($task));
            }
        });
    }
}
