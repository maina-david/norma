<?php

namespace App\Services\Workflows;

use App\Actions\Workflows\Task\SetDependentComputedUnits;
use App\Actions\Workflows\Task\SetDependentRelativeField;
use App\Enums\Workflows\ComputedTaskAction;
use App\Enums\Workflows\RelativeTaskAction;
use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;

class TaskActionManager
{
    /**
     * Handle the transition.
     *
     * @param Task $task
     *
     * @return void
     */
    public static function validateAndTrigger(Task $task): void
    {
        $task->load(['board']);

        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? null;

        if (!$defaults || !$task->isDirty(['units', 'task_status', 'complexity'])) {
            return;
        }

        self::handleComplexity($task, $defaults);
        self::handleUnits($task, $defaults);
    }

    /**
     * Handle the setting of complexity.
     *
     * @param Task                  $task
     * @param array<string, string> $defaults
     *
     * @return void
     */
    protected static function handleComplexity(Task $task, array $defaults): void
    {
        if ($task->isClean('complexity')) {
            return;
        }

        if ($defaults['set_dependent_complexity'] ?? false) {
            (new SetDependentRelativeField())
                ->usingField('complexity', 'set_dependent_complexity')
                ->handle($task, RelativeTaskAction::OWN->value);
        }
    }

    /**
     * Handle the setting of units.
     *
     * @param Task                  $task
     * @param array<string, string> $defaults
     *
     * @return void
     */
    protected static function handleUnits(Task $task, array $defaults): void
    {
        $type = $defaults['set_dependent_units_type'] ?? null;
        $complexityOrStatus = $task->isDirty(['task_status', 'complexity']) && TaskStatus::done()->is($task->task_status);

        if (!$type) {
            return;
        }

        if (ComputedTaskAction::tryFrom($type) && $complexityOrStatus) {
            (new SetDependentComputedUnits())->handle($task, $type);

            return;
        }

        if (RelativeTaskAction::tryFrom($type)) {
            (new SetDependentRelativeField())->handle($task, $type);
        }
    }
}
