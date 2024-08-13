<?php

namespace App\Http\ResourceActions\Workflows;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\ResourceActionWithForm;
use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChangeStatus implements ResourceAction
{
    use ResourceActionWithForm;

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.workflows.task.override-status');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.task.change_status');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'change-status';
    }

    /**
     * Get the path to the form view.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return string
     */
    protected function getFormView(Request $request, array $resourceIds): string
    {
        return 'partials.workflows.collaborate.task.actions.change-status';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function handle(Request $request, array $resourceIds): RedirectResponse
    {
        $status = $request->get('task_status');

        Task::whereKey($resourceIds)
            ->get()
            ->each(function (Task $task) use ($status) {
                $task->task_status = $status;
                $task->save();
            });

        Session::flash('flash.message', __('workflows.task.successfully_changed_status'));

        return back();
    }
}
