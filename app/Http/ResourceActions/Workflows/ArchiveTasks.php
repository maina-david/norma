<?php

namespace App\Http\ResourceActions\Workflows;

use App\Contracts\Http\ResourceAction;
use App\Enums\Workflows\TaskStatus;
use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ArchiveTasks implements ResourceAction
{
    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.workflows.task.archive');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.task.archive');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'archive';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        Task::whereKey($resourceIds)
            ->get()
            ->each(function (Task $task) {
                $task->task_status = TaskStatus::archive()->value;
                $task->save();
            });

        Session::flash('flash.message', __('workflows.task.successfully_archived'));

        return back();
    }
}
