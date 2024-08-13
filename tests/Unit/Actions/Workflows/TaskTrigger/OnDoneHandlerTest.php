<?php

namespace Tests\Unit\Actions\Workflows\TaskTrigger;

use App\Actions\Workflows\TaskTrigger\OnDoneHandler;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTrigger;
use Exception;
use Tests\TestCase;

class OnDoneHandlerTest extends TestCase
{
    private function createTriggerForTest(): TaskTrigger
    {
        $task = Task::factory()->create(['task_status' => TaskStatus::done()->value]);
        $task2 = Task::factory()->create(['task_status' => TaskStatus::done()->value]);

        /** @var TaskTrigger */
        return TaskTrigger::factory()->create([
            'source_task_id' => $task->id,
            'target_task_id' => $task2->id,
            'trigger_type' => TaskTriggerType::ON_DONE->value,
            'details' => ['update_field' => 'due_date', 'update_field_value' => 'P4W'],
        ]);
    }

    public function testHandleException(): void
    {
        $trigger = $this->createTriggerForTest();
        $trigger->update(['trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value]);

        $this->expectException(Exception::class);
        app(OnDoneHandler::class)->handle($trigger);
    }

    public function testHandleTriggered(): void
    {
        $trigger = $this->createTriggerForTest();

        $trigger->update(['triggered' => true]);
        $dueDate = $trigger->targetTask->due_date;
        app(OnDoneHandler::class)->handle($trigger);
        $this->assertSame($dueDate->toDateTimeString(), $trigger->targetTask->refresh()->due_date->toDateTimeString());
    }

    public function testHandleUpdate(): void
    {
        $trigger = $this->createTriggerForTest();

        $dueDate = $trigger->targetTask->due_date;
        app(OnDoneHandler::class)->handle($trigger);
        $this->assertNotSame($dueDate, $trigger->targetTask->refresh()->due_date);
    }
}
