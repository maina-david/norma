<?php

namespace Tests\Unit\Actions\Workflows\TaskTrigger;

use App\Actions\Workflows\TaskTrigger\OnStatusChangeHandler;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTrigger;
use Exception;
use Tests\TestCase;

class OnStatusChangeHandlerTest extends TestCase
{
    private function createTriggerForTest(): TaskTrigger
    {
        $task = Task::factory()->create(['task_status' => TaskStatus::inProgress()->value]);
        $task2 = Task::factory()->create(['task_status' => TaskStatus::todo()->value]);

        /** @var TaskTrigger */
        return TaskTrigger::factory()->create([
            'source_task_id' => $task->id,
            'target_task_id' => $task2->id,
            'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value,
            'details' => ['task_status' => TaskStatus::inProgress()->value, 'update_field' => 'task_status', 'update_field_value' => TaskStatus::inProgress()->value],
        ]);
    }

    public function testHandleException(): void
    {
        $trigger = $this->createTriggerForTest();
        $trigger->update(['trigger_type' => TaskTriggerType::ON_DONE->value]);

        $this->expectException(Exception::class);
        app(OnStatusChangeHandler::class)->handle($trigger);
    }

    public function testHandleUpdate(): void
    {
        $trigger = $this->createTriggerForTest();

        app(OnStatusChangeHandler::class)->handle($trigger);
        $this->assertSame(TaskStatus::inProgress()->value, $trigger->targetTask->task_status);
    }
}
