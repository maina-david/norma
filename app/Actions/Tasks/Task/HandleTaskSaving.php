<?php

namespace App\Actions\Tasks\Task;

use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\HTMLPurifierService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleTaskSaving
{
    use AsAction;

    /**
     * @param HTMLPurifierService $purifier
     **/
    public function __construct(protected HTMLPurifierService $purifier)
    {
    }

    public function handle(Task $task): void
    {
        $this->handleDirty($task);
    }

    public function handleDirty(Task $task): void
    {
        /** @var User|null */
        $user = Auth::user();

        if (!is_null($user)) {
            if ($task->isDirty('due_on')) {
                $this->updateTimezone($task, $user);
            }

            if ($task->isDirty('task_status')) {
                $task->task_status = (int) $task->task_status;
                $task->completed_at = $task->task_status === TaskStatus::done()->value ? now() : null;
            }

            if ($task->isDirty('description') && !is_null($task->description)) {
                $task->description = $this->purifier->cleanTaskDescription($task->description);
            }
        }
    }

    protected function updateTimezone(Task $task, User $user): void
    {
        $timezone = $user->timezone ?? 'UTC';
        $task->due_on = $task->due_on ? Carbon::parse($task->due_on, $timezone)->setTimezone('UTC') : null;
    }
}
