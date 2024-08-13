<?php

namespace App\Actions\Workflows\TaskTrigger;

use App\Actions\Workflows\Task\SetOwnComputedUnits;
use App\Actions\Workflows\Task\SetOwnRelativeField;
use App\Enums\Workflows\ComputedTaskAction;
use App\Enums\Workflows\RelativeTaskAction;
use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTrigger;
use DateInterval;
use Exception;
use Illuminate\Support\Carbon;

class AbstractHandler
{
    public function updateField(TaskTrigger $trigger): void
    {
        $updatefield = $trigger->details['update_field'] ?? null;
        $updateFieldValue = $trigger->details['update_field_value'] ?? null;
        if (is_null($updatefield) || is_null($updateFieldValue)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        if ($updatefield === 'due_date') {
            $this->updateDueDate($trigger);

            return;
        }
        if ($updatefield === 'units') {
            $this->updateUnits($trigger);

            return;
        }

        /** @var Task|null */
        $targetTask = (new Task())->newQueryWithoutScopes()->find($trigger->target_task_id);

        /** @var array<string, mixed> $data */
        $data = [$updatefield => $updateFieldValue];

        if ($targetTask && !(TaskStatus::archive()->is($targetTask->task_status) && $updatefield === 'task_status')) {
            $targetTask->update($data);
        }
    }

    public function updateDueDate(TaskTrigger $trigger): void
    {
        try {
            $data['due_date'] = Carbon::now('UTC')->add(new DateInterval($trigger->details['update_field_value'] ?? 'P1W'));
            /** @var Task|null */
            $targetTask = (new Task())->newQueryWithoutScopes()->find($trigger->target_task_id);
            if ($targetTask) {
                $targetTask->update($data);
            }
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
        }
        // @codeCoverageIgnoreEnd
    }

    public function updateUnits(TaskTrigger $trigger): void
    {
        /** @var Task $task */
        $task = (new Task())->newQueryWithoutScopes()
            ->with(['board'])
            ->find($trigger->source_task_id);

        $type = $trigger->details['update_field_value'] ?? 0;

        if (ComputedTaskAction::tryFrom($type)) {
            (new SetOwnComputedUnits())->handle($task, $type);

            return;
        }

        if (RelativeTaskAction::tryFrom($type)) {
            (new SetOwnRelativeField())->usingField('units', 'set_current_units_multiple')->handle($task, $type);
        }
    }
}
