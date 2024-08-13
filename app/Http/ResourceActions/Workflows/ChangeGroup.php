<?php

namespace App\Http\ResourceActions\Workflows;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\ResourceActionWithForm;
use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChangeGroup implements ResourceAction
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
        return $user && $user->can('collaborate.workflows.task.update');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.task.change_group');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'change-group';
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
        return 'partials.workflows.collaborate.task.actions.change-group';
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
        $group = $request->get('group_id');

        Task::whereKey($resourceIds)
            ->get()
            ->each(function (Task $task) use ($group) {
                $task->group_id = $group;
                $task->save();
            });

        Session::flash('flash.message', __('workflows.task.successfully_changed_group'));

        return back();
    }
}
