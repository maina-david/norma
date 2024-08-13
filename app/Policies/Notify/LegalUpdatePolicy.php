<?php

namespace App\Policies\Notify;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class LegalUpdatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can edit the update.
     *
     * @param User        $user
     * @param LegalUpdate $update
     * @param Task|null   $task
     *
     * @return bool
     */
    public function create(User $user, LegalUpdate $update, ?Task $task = null): bool
    {
        if ($user->can('collaborate.notify.legal-update.manage-without-task')) {
            return true;
        }

        // @codeCoverageIgnoreStart
        return $task && $user->can('view', $task) && $user->can('collaborate.notify.legal-update.create');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine whether the user can edit the update.
     *
     * @param User        $user
     * @param LegalUpdate $update
     * @param Task|null   $task
     *
     * @return bool
     */
    public function view(User $user, LegalUpdate $update, ?Task $task = null): bool
    {
        if (
            $user->can('collaborate.notify.legal-update.manage-without-task')
            || $user->can('collaborate.notify.legal-update.view-without-task')
        ) {
            return true;
        }

        // @codeCoverageIgnoreStart
        if (!$task && request('task')) {
            /** @var Task|null $task */
            $task = Task::whereKey(request('task'))->first();
        }

        $task?->load(['document']);

        $forDoc = $task && $task->document?->legal_update_id === $update->id;
        $isUnassigned = $task && !$task->user_id;

        return $forDoc && (($isUnassigned && $user->can('collaborate.notify.legal-update.view')) || $user->can('view', $task));
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine whether the user can edit the update.
     *
     * @param User        $user
     * @param LegalUpdate $update
     * @param Task|null   $task
     *
     * @return bool
     */
    public function edit(User $user, LegalUpdate $update, ?Task $task = null): bool
    {
        if ($user->can('collaborate.notify.legal-update.manage-without-task')) {
            return true;
        }

        // @codeCoverageIgnoreStart
        $task?->load(['document']);

        return $task && $task->document?->legal_update_id === $update->id && $user->can('view', $task);
        // @codeCoverageIgnoreEnd
    }
}
