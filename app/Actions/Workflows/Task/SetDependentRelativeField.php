<?php

namespace App\Actions\Workflows\Task;

use App\Enums\Workflows\RelativeTaskAction;
use App\Models\Workflows\Task;

class SetDependentRelativeField extends SetOwnRelativeField
{
    /**
     * Execute the task action.
     *
     * @param Task       $task
     * @param mixed|null $payload
     *
     * @return void
     */
    public function handle(Task $task, mixed $payload = null): void
    {
        $task->load(['dependedBy']);

        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? null;

        if (!$defaults || RelativeTaskAction::IGNORE->value == $payload || empty($task->dependedBy)) {
            return;
        }

        $task->dependedBy->each(fn ($item) => $item->update([$this->field => $this->getUnits($task, $payload, $defaults)]));
    }
}
