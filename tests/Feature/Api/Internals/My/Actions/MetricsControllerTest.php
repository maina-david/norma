<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Enums\Tasks\TaskStatus;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class MetricsControllerTest extends MyTestCase
{
    public function testAllMetrics(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $action = ActionArea::factory()->create();
        $ref = Reference::factory()->create();
        $ref->actionAreas()->attach($action->id);
        $task = Task::factory()->create([
            'place_id' => $norma->id,
            'taskable_type' => $ref->getMorphClass(),
            'taskable_id' => $ref->id,
            'task_status' => TaskStatus::notStarted()->value,
            'impact' => 1,
        ]);

        $this->getJson(route('api.my.actions.metrics.statuses'))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    TaskStatus::notStarted()->value => 1,
                    TaskStatus::inProgress()->value => 0,
                    TaskStatus::done()->value => 0,
                    TaskStatus::paused()->value => 0,
                ],
            ]);

        $this->getJson(route('api.my.actions.metrics.impact'))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    TaskStatus::notStarted()->value => 1,
                    TaskStatus::inProgress()->value => 0,
                    TaskStatus::done()->value => 0,
                    TaskStatus::paused()->value => 0,
                    4 => 0,
                ],
            ]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'total']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 1]]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'overdue']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 0]]);

        $task->update(['due_on' => now()->subMonths(1)]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'overdue']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 1]]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'complete-rate']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 0]]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'incomplete-rate']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 100]]);

        $task->update(['task_status' => TaskStatus::done()->value]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'complete-rate']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 100]]);

        $this->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'incomplete-rate']))
            ->assertSuccessful()
            ->assertJson(['data' => ['value' => 0]]);

        $this->withExceptionHandling()
            ->getJson(route('api.my.actions.metrics.single.show', ['metric' => 'fake-metric']))
            ->assertNotFound();

        $this->getJson(route('api.my.actions.metrics.creation-completion'))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'labels' => [
                        now()->format('Y-m'),
                    ],
                    'completed' => [
                        1,
                    ],
                    'created' => [
                        [
                            'x' => now()->format('Y-m'),
                            'y' => 1,
                        ],
                    ],
                ],
            ]);
    }
}
