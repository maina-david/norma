<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\TaskTypeChanged;
use App\Models\Comments\Collaborate\Comment;

class TaskTypeChangedSubscriber
{
    /**
     * Listen to the assignee changed event.
     *
     * @param TaskTypeChanged $event
     *
     * @return void
     */
    public function handle(TaskTypeChanged $event): void
    {
        Comment::where('task_id', $event->task->id)->update(['task_type_id' => $event->task->task_type_id]);
    }
}
