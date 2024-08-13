<?php

namespace App\Http\ResourceActions\Tasks;

use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class ChangeImpact implements ResourceAction
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
        return __('tasks.change_impact');
    }

    /**
     * {@inheritDoc}
     */
    public function actionId(): string
    {
        return 'change_impact';
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse|ResponseFactory
    {
        $value = (int) $request->get($this->actionId());

        if (!empty($resourceIds) && $value > 0 && $value <= 10) {
            Task::whereKey($resourceIds)->chunk(100, function ($tasks) use ($value) {
                $tasks->each(fn ($task) => $task->update(['impact' => $value]));
            });
        }

        /** @var ResponseFactory */
        return response();
    }
}
