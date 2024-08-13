<?php

namespace App\Actions\Workflows\Task;

use App\Models\Workflows\Task;

class SetDependentComputedUnits extends SetOwnComputedUnits
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
        $task->load(['dependedBy.document']);

        if (count($task->dependedBy) < 1) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $task->dependedBy->each(function ($item) use ($payload) {
            /** @var Task $item */
            parent::handle($item, $payload);
        });
    }
}
