<?php

namespace App\Policies\Workflows;

use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Authorise the viewing of the task.
     *
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->can('collaborate.workflows.task.viewAny')) {
            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        if ($task->isAssigneeOnDependant() && $task->requires_rating) {
            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        $canAccessTaskType = !$task->taskType || $user->can($task->taskType->permission(true));

        return $canAccessTaskType && $task->user_id === $user->id;
    }

    /**
     * Check if the user can rate the task.
     *
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function rate(User $user, Task $task): bool
    {
        return $task->canRate();
    }
}
