<?php

namespace Tests\Unit\Actions\Payments\PaymentRequest;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Payments\PaymentRequest;
use App\Models\Workflows\Task;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class CreateForTaskTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testHandleNoAssignee(): void
    {
        $task = Task::factory()->create();
        $assignee = $task->assignee;
        $task->update(['user_id' => null]);
        $task->refresh();
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('The task is not assigned to anyone');
        app(CreateForTask::class)->handle($task);
        $task->update(['user_id' => $assignee->id]);
    }

    public function testHandlePaymentRequests(): void
    {
        $task = Task::factory()->create();
        $request = PaymentRequest::factory()->for($task)->create();
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('The task already has payment requests');
        app(CreateForTask::class)->handle($task);
    }

    public function testHandleCurrencyError(): void
    {
        $task = Task::factory()->create();
        $team = Team::factory()->create(['currency' => null]);
        $task->assignee->profile->update(['team_id' => $team->id]);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('The team has no currency set');
        app(CreateForTask::class)->handle($task);
    }

    public function testHandleRateError(): void
    {
        $task = Task::factory()->create();
        $team = Team::factory()->create();
        $task->assignee->profile->update(['team_id' => $team->id]);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Could not determine the rate for this task');
        app(CreateForTask::class)->handle($task);
    }

    public function testHandle(): void
    {
        $task = Task::factory()->create(['units' => 0]);
        $team = Team::factory()->create();
        $task->assignee->profile->update(['team_id' => $team->id]);
        $teamRate = TeamRate::factory()
            ->for($team, 'team')
            ->for($task->taskType, 'taskType')
            ->create();

        $this->expectExceptionMessage('Could not determine the units for this task');

        $request = app(CreateForTask::class)->handle($task);

        $task->update(['units' => 2]);
        $this->assertInstanceOf(PaymentRequest::class, $request);
        $this->assertSame($teamRate->rate_per_unit, $request->rate_per_unit);
        $this->assertSame($task->units, $request->units);
    }
}
