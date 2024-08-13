<?php

namespace Tests\Feature\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Pivots\TaskRating;
use App\Models\Workflows\RatingType;
use App\Models\Workflows\Task;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    public function testSavingAndDeletingRatings(): void
    {
        $assignee = Collaborator::factory()->create();

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $task = Task::factory()->create(['user_id' => $assignee->id, 'requires_rating' => true]);

        $types = RatingType::factory()->count(3)->create();

        $task->taskType->ratingGroup->ratingTypes()->attach($types->pluck('id')->toArray());

        $types = $task->taskType->ratingGroup->ratingTypes->keyBy('id')
            ->map(fn ($type) => ['score' => 4, 'comments' => 'comment here'])
            ->toArray();

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => TaskStatus::done()->value]))
            ->assertRedirect();

        $this->assertFalse(TaskStatus::done()->is($task->refresh()->task_status));

        $this->post(route('collaborate.task.rate', ['task' => $task->id]), ['rating' => $types])
            ->assertRedirect(route('collaborate.tasks.show', ['task' => $task->id]));

        $this->assertDatabaseHas(TaskRating::class, ['task_id' => $task->id, 'score' => 4]);

        $this->delete(route('collaborate.task.ratings.delete', ['task' => $task]))
            ->assertRedirect();

        $this->assertDatabaseMissing(TaskRating::class, ['task_id' => $task->id]);
    }
}
