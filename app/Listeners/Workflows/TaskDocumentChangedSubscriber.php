<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\TaskDocumentChanged;
use App\Models\Workflows\Task;

class TaskDocumentChangedSubscriber
{
    /**
     * Listen to the assignee changed event.
     *
     * @param TaskDocumentChanged $event
     *
     * @return void
     */
    public function handle(TaskDocumentChanged $event): void
    {
        if ($event->task->isParent()) {
            Task::where('parent_task_id', $event->task->id)->update(['document_id' => $event->task->document_id]);

            return;
        }

        Task::where('id', $event->task->parent_task_id)
            ->orWhere('parent_task_id', $event->task->parent_task_id)
            ->update(['document_id' => $event->task->document_id]);
    }
}
