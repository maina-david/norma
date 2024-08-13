<?php

namespace App\Http\ResourceActions\Tasks;

use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class ChangeAssignee implements ResourceAction
{
    use DecodesHashids;

    /**
     * {@inheritDoc}
     */
    public function authorise(?User $user): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return __('workflows.task.change_assignee');
    }

    /**
     * {@inheritDoc}
     */
    public function actionId(): string
    {
        return 'change_assignee';
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory
    {
        $value = $request->get($this->actionId());

        if (!is_null($value) && !empty($resourceIds)) {
            /** @var User $assignee */
            $assignee = $this->decodeHash($value, User::class);

            Task::whereKey($resourceIds)->chunk(100, function ($tasks) use ($assignee) {
                $tasks->each(fn ($task) => $task->update(['assigned_to_id' => $assignee->id]));
            });
        }

        /** @var ResponseFactory */
        return response();
    }
}
