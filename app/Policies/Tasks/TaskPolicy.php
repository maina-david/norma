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
        return $task->libryo && $user->hasLibryoAccess($task->libryo);
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
        $task->load('libryo.organisation');

        return $task->author?->is($user) || (isset($task->libryo->organisation) && $user->isOrganisationAdmin($task->libryo->organisation));
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
