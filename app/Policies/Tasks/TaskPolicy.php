<?php

namespace App\Policies\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function view(User $user, Task $task): bool
    {
        return $task->norma && $user->hasNormaAccess($task->norma);
    }

    /**
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    /**
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    public function delete(User $user, Task $task): bool
    {
        $task->load('norma.organisation');

        return $task->author?->is($user) || (isset($task->norma->organisation) && $user->isOrganisationAdmin($task->norma->organisation));
    }

    /**
     * @param User $user
     * @param Task $task
     *
     * @return bool
     */
    // public function changeStatus(User $user, Task $task): bool
    // {
    //     return $task->author?->is($user) || $task->assignee?->is($user);
    // }
}
