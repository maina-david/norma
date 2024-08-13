<?php

namespace App\Policies\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\TaskProject;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskProjectPolicy
{
    use HandlesAuthorization;

    /**
     * @param User        $user
     * @param TaskProject $project
     *
     * @return bool
     */
    public function view(User $user, TaskProject $project): bool
    {
        return is_null($project->organisation) ? false : $user->isInOrganisation($project->organisation);
    }

    /**
     * @param User        $user
     * @param TaskProject $project
     *
     * @return bool
     */
    public function update(User $user, TaskProject $project): bool
    {
        return $this->view($user, $project);
    }

    /**
     * @param User        $user
     * @param TaskProject $project
     *
     * @return bool
     */
    public function delete(User $user, TaskProject $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * @param User        $user
     * @param TaskProject $project
     *
     * @return bool
     */
    public function archive(User $user, TaskProject $project): bool
    {
        return $this->update($user, $project);
    }
}
