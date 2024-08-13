<?php

namespace Tests\Feature\Api\Internals\My\Activities;

use App\Enums\Tasks\TaskStatus;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use Tests\Feature\My\MyTestCase;

class ActivityControllerTest extends MyTestCase
{
    public function testListingActivities(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->create(['place_id' => $libryo->id, 'task_status' => TaskStatus::notStarted()->value]);

        $task->update(['task_status' => TaskStatus::inProgress()->value]);

        $this->assertDatabaseHas(TaskActivity::class, ['task_id' => $task->id]);

        $this->getJson(route('api.my.activities.related.index', ['relation' => 'task', 'related' => $task->id]))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'description' => "{$user->full_name} changed the task status from Not Started to In Progress",
                    ],
                ],
            ]);
    }
}
