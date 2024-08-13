<?php

namespace App\Actions\Tasks\Task;

use App\Events\Auth\UserActivity\CreatedTask;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Notifications\Tasks\TaskAssignedNotification;
use App\Stores\Tasks\TaskActivityStore;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleTaskCreated
{
    use AsAction;

    /**
     * @param TaskActivityStore $taskActivityStore
     */
    public function __construct(protected TaskActivityStore $taskActivityStore)
    {
    }

    public function handle(Task $task): void
    {
        /** @var User|null */
        $user = Auth::user();

        if (!is_null($user)) {
            CreatedTask::dispatch($task);

            if ($task->assignee && $task->assignee->isNot($user)) {
                $transKey = 'notifications.task_assigned_subject_notification';
                $task->assignee->notify(
                    new TaskAssignedNotification($task, $user->id, $task->assignee->id, $transKey)
                );
            }
        }
    }
}
