<?php

namespace Tests\Unit\Actions\Workflows\TaskTransition;

use App\Actions\Workflows\TaskTransition\HandleCreated;
use App\Enums\Workflows\TaskStatus;
use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskTrigger;
use App\Services\Workflows\TaskTriggerManager;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class HandleCreatedTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $this->partialMock(TaskTriggerManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('triggerFromTransition');
        });
        $task = Task::factory()->create(['auto_payment' => true, 'task_status' => TaskStatus::done()->value]);
        $taskChild = Task::factory()->create(['auto_payment' => true, 'task_status' => TaskStatus::inProgress()->value, 'parent_task_id' => $task->id, 'user_id' => $task->assignee->id]);
        $transition = TaskTransition::factory()->for($task)->make(['task_status' => TaskStatus::done()->value, 'transitioned_field' => 'task_status']);

        $team = Team::factory()->create();
        $task->assignee->profile->update(['team_id' => $team->id]);
        $teamRate = TeamRate::factory()
            ->for($task->assignee->profile->team, 'team')
            ->create(['for_task_type_id' => $task->taskType->id]);

        $trigger = TaskTrigger::factory()->create(['source_task_id' => $task->id]);

        app(HandleCreated::class)->handle($transition);

        // auto payment should trigger creation of payment requests
        $this->assertTrue($task->paymentRequests()->count() > 0);

        Log::shouldReceive('error')
            ->withArgs(function ($message) {
                return str_contains($message, 'Auto payment for collaborate task failed');
            });
        app(HandleCreated::class)->handle($transition);
    }
}
