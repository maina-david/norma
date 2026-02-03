<?php

namespace App\Stores\Tasks;

use App\Enums\Tasks\TaskActivityType;
use App\Models\Tasks\TaskActivity;

class TaskActivityStore
{
    /**
     * @param TaskActivityType $type
     * @param int              $taskId
     * @param int              $userId
     * @param int              $normaId
     * @param array<mixed>     $details
     *
     * @return TaskActivity
     */
    public function create(
        TaskActivityType $type,
        int $taskId,
        int $userId,
        int $normaId,
        array $details,
    ): TaskActivity {
        $data = [
            'task_id' => $taskId,
            'place_id' => $normaId,
            'user_id' => $userId,
            'activity_type' => $type->value,
            'details' => $details,
        ];

        /** @var TaskActivity */
        return TaskActivity::create($data);
    }
}
