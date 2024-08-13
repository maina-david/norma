<?php

namespace App\Services\Workflows;

use App\Actions\Workflows\TaskTrigger\OnDoneHandler;
use App\Actions\Workflows\TaskTrigger\OnStatusChangeHandler;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskTrigger;

class TaskTriggerManager
{
    public function triggerFromTransition(TaskTransition $transition): void
    {
        /** @var Task */
        $task = (new Task())->newQueryWithoutScopes()->find($transition->task_id);
        $task->triggers->load('sourceTask');
        foreach ($task->triggers as $trigger) {
            if ($trigger->shouldTrigger($transition)) {
                $this->trigger($trigger);
            }
        }
    }

    public function trigger(TaskTrigger $trigger): void
    {
        $handler = null;
        switch ($trigger->trigger_type) {
            case TaskTriggerType::ON_DONE->value:
                $handler = app(OnDoneHandler::class);

                break;
            case TaskTriggerType::ON_STATUS_CHANGE->value:
                $handler = app(OnStatusChangeHandler::class);

                break;
                // @codeCoverageIgnoreStart
            default:
                break;
                // @codeCoverageIgnoreEnd
        }

        if ($handler) {
            $handler->handle($trigger);
            $trigger->update(['triggered' => true]);
        }
    }
}
