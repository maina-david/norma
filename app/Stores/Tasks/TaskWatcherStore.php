<?php

namespace App\Stores\Tasks;

use App\Enums\Tasks\TaskActivityType;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Stores\Traits\AttachesDetaches;

class TaskWatcherStore
{
    use AttachesDetaches;

    public function __construct(protected TaskActivityStore $taskActivityStore)
    {
    }

    public function watch(Task $task, User $user): void
    {
        $this->attachRelations($task, 'watchers', collect([$user]));
        $details = [
            'watching' => true,
        ];
        $this->taskActivityStore->create(TaskActivityType::watchTask(), $task->id, $user->id, $task->place_id, $details);
    }

    public function unwatch(Task $task, User $user): void
    {
        $this->detachRelations($task, 'watchers', collect([$user]));
        $details = [
            'watching' => false,
        ];
        $this->taskActivityStore->create(TaskActivityType::watchTask(), $task->id, $user->id, $task->place_id, $details);
    }
}
