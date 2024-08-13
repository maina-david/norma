<?php

namespace App\Http\ResourceActions\Tasks;

use App\Contracts\Http\ResourceAction;
use App\Enums\Tasks\TaskPriority;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class ChangePriority implements ResourceAction
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
        return __('workflows.task.change_priority');
    }

    /**
     * {@inheritDoc}
     */
    public function actionId(): string
    {
        return 'change_priority';
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory
    {
        $value = $request->get($this->actionId());

        if (!is_null($value) && in_array($value, array_keys(TaskPriority::lang())) && !empty($resourceIds)) {
            $priority = TaskPriority::fromValue((int) $value);

            Task::whereKey($resourceIds)->chunk(100, function ($tasks) use ($priority) {
                $tasks->each(fn ($task) => $task->update(['priority' => $priority->value]));
            });
        }

        /** @var ResponseFactory */
        return response();
    }
}
