<?php

namespace Tests\Unit\Services\Workflows;

use App\Actions\Workflows\TaskTrigger\OnDoneHandler;
use App\Actions\Workflows\TaskTrigger\OnStatusChangeHandler;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskTrigger;
use App\Services\Workflows\TaskTriggerManager;
use Mockery\MockInterface;
use Tests\TestCase;

class TaskTriggerManagerTest extends TestCase
{
    public function testTriggerFromTransition(): void
    {
        $this->partialMock(OnDoneHandler::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });
        $this->partialMock(OnStatusChangeHandler::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });

        $task = Task::factory()->create(['task_status' => TaskStatus::done()->value]);
        $transition = TaskTransition::factory()
            ->for($task)
            ->make(['task_status' => TaskStatus::done()->value, 'transitioned_field' => 'task_status']);

        $trigger = TaskTrigger::factory()->create(['source_task_id' => $task->id, 'trigger_type' => TaskTriggerType::ON_DONE->value]);
        app(TaskTriggerManager::class)->triggerFromTransition($transition);

        $this->assertTrue($trigger->refresh()->triggered);

        $transition = TaskTransition::factory()
            ->for($task)
            ->make(['task_status' => TaskStatus::done()->value, 'transitioned_field' => 'task_status']);

        $trigger = TaskTrigger::factory()->create(['source_task_id' => $task->id, 'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value, 'details' => ['task_status' => $task->task_status]]);
        app(TaskTriggerManager::class)->triggerFromTransition($transition);

        $this->assertTrue($trigger->refresh()->triggered);
    }
}
