<?php

namespace App\Actions\Workflows\TaskTrigger;

use App\Contracts\Workflows\TaskTrigger\TriggerHandlerInterface;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\TaskTrigger;
use Exception;

class OnDoneHandler extends AbstractHandler implements TriggerHandlerInterface
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
        if ($trigger->trigger_type !== TaskTriggerType::ON_DONE->value || $trigger->sourceTask?->task_status !== TaskStatus::done()->value) {
            throw new Exception('Cannot trigger this trigger');
        }
        if ($trigger->triggered) {
            return;
        }

        if (!is_null($trigger->details['update_field'] ?? null)) {
            $this->updateField($trigger);
        }
    }
}
