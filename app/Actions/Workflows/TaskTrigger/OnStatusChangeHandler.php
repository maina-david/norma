<?php

namespace App\Actions\Workflows\TaskTrigger;

use App\Contracts\Workflows\TaskTrigger\TriggerHandlerInterface;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\TaskTrigger;
use Exception;

class OnStatusChangeHandler extends AbstractHandler implements TriggerHandlerInterface
{
    /**
     * Trigger the given ON_DONE trigger.
     *
     * @param TaskTrigger $trigger
     *
     * @throws Exception
     *
     * @return void
     **/
    public function handle(TaskTrigger $trigger): void
    {
        if ($trigger->trigger_type !== TaskTriggerType::ON_STATUS_CHANGE->value || is_null($trigger->details['task_status'] ?? null)) {
            throw new Exception('Wrong trigger type');
        }

        // @phpstan-ignore-next-line
        if ($trigger->triggered || !$trigger->details) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $taskStatus = isset($trigger->details['task_status']) ? (int) $trigger->details['task_status'] : null;

        if ($taskStatus && $taskStatus !== $trigger->sourceTask?->task_status) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if (
            $taskStatus
            && !is_null($trigger->details['update_field'] ?? null)
            && $taskStatus === $trigger->sourceTask?->task_status
        ) {
            $this->updateField($trigger);
        }
    }
}
