<?php

namespace App\Http\ResourceActions\Tasks;

use App\Contracts\Http\ResourceAction;
use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class ChangeStatus implements ResourceAction
{
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
        return __('workflows.task.change_status');
    }

    /**
     * {@inheritDoc}
     */
    public function actionId(): string
    {
        return 'change_status';
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory
    {
        $status = $request->get($this->actionId());

        if (!is_null($status) && in_array($status, array_keys(TaskStatus::lang())) && !empty($resourceIds)) {
            $status = TaskStatus::fromValue((int) $status);

            Task::whereKey($resourceIds)->chunk(100, function ($tasks) use ($status) {
                $tasks->each(fn ($task) => $task->update(['task_status' => $status->value]));
            });
        }

        /** @var ResponseFactory */
        return response();
    }
}
