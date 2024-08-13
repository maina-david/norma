<?php

namespace App\Traits\Workflows;

use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTransition;

trait CreatesTransitions
{
    /**
     * Create a new task transition.
     *
     * @param Task   $task
     * @param string $field
     *
     * @return TaskTransition
     */
    public function createTransition(Task $task, string $field): TaskTransition
    {
        /** @var TaskTransition */
        return TaskTransition::create([
            'task_id' => $task->id,
            'user_id' => $task->user_id,
            'task_status' => $task->task_status,
            'complexity' => $task->complexity,
            'transitioned_field' => $field,
        ]);
    }
}
