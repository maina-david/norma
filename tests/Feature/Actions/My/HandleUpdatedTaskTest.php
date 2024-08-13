<?php

namespace Tests\Feature\Actions\My;

use App\Enums\Tasks\TaskStatus;
use App\Models\Tasks\Task;
use RRule\RRule;
use Tests\Feature\My\MyTestCase;

class HandleUpdatedTaskTest extends MyTestCase
{
    public function testHandleTaskRecurrence()
    {
        [$user, $libryo] = $this->initUserLibryoOrg();

        $rrule = new RRule([
            'FREQ' => 'DAILY',
            'COUNT' => 3,
        ]);

        // When $task->next->doesntExist is true and $task->rrule is not empty
        $task1 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'rrule' => $rrule->rfcString(),
        ]);

        $this->putJson(route('api.my.actions.tasks.update', ['task' => $task1->hash_id]), ['task_status' => TaskStatus::done()->value])
            ->assertSuccessful();

        // When Recurrence Start Date is already passed
        $task2 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'rrule' => $rrule->rfcString(),
            'recurrence_start_date' => now()->subDays(10)->format('Y-m-d'),
        ]);

        $this->putJson(route('api.my.actions.tasks.update', ['task' => $task2->hash_id]), ['task_status' => TaskStatus::done()->value])
            ->assertSuccessful();

        // When $rruleCount is reached
        $task3 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'recurrence_start_date' => now()->subDays(3)->format('Y-m-d'),
            'task_status' => TaskStatus::done()->value,
            'rrule' => $rrule->rfcString(),
            'recurrence_count' => 0,
        ]);

        $task4 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'series_task_id' => $task3->id,
            'recurrence_start_date' => now()->subDays(3)->format('Y-m-d'),
            'task_status' => TaskStatus::done()->value,
            'previous_task_id' => $task3->id,
            'due_on' => now()->subDays(2)->format('Y-m-d'),
            'rrule' => $rrule->rfcString(),
            'recurrence_count' => 1,
        ]);

        $task5 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'series_task_id' => $task3->id,
            'recurrence_start_date' => now()->subDays(3)->format('Y-m-d'),
            'task_status' => TaskStatus::done()->value,
            'previous_task_id' => $task4->id,
            'due_on' => now()->subDays(1)->format('Y-m-d'),
            'rrule' => $rrule->rfcString(),
            'recurrence_count' => 2,
        ]);

        $task6 = Task::factory()->create([
            'place_id' => $libryo->id,
            'assigned_to_id' => $user->id,
            'series_task_id' => $task3->id,
            'recurrence_start_date' => now()->subDays(3)->format('Y-m-d'),
            'task_status' => TaskStatus::inProgress()->value,
            'previous_task_id' => $task5->id,
            'due_on' => now()->format('Y-m-d'),
            'rrule' => $rrule->rfcString(),
            'recurrence_count' => 3,
        ]);

        $this->putJson(route('api.my.actions.tasks.update', ['task' => $task6->hash_id]), ['task_status' => TaskStatus::done()->value])
            ->assertSuccessful();
    }
}
