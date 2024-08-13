<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskRepeatInterval;
use App\Enums\Tasks\TaskStatus;
use App\Jobs\Tasks\CreateRecurringTasks;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class CreateRecurringTasksTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testCreatingTasks(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Task $task */
        $task = Task::factory()->create([
            'due_on' => now()->addDay()->startOfDay(),
            'task_status' => TaskStatus::notStarted()->value,
            'frequency' => 1,
            'frequency_interval' => TaskRepeatInterval::MONTH,
        ]);

        CreateRecurringTasks::dispatch();

        $this->assertDatabaseMissing(Task::class, ['previous_task_id' => $task->id]);

        $this->travelTo(now()->addMonth());

        CreateRecurringTasks::dispatch();

        $this->assertDatabaseHas(Task::class, [
            'previous_task_id' => $task->id,
            'due_on' => now()->addDay()->startOfDay()->toDateTimeString(),
        ]);
    }
}
