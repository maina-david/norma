<?php

namespace Tests\Feature\Api\V3\Tasks;

use App\Models\Customer\Norma;
use App\Models\Tasks\Task;
use Tests\Feature\Api\V3\ApiV3TestCase;

class TaskControllerTest extends ApiV3TestCase
{
    public function testTasksForOrganisation(): void
    {
        /** @var Norma $norma */
        [$norma, $organisation, $work, $updatesCollection, $domain, $tag, $childWork] = $this->initCompiledStream();

        $otherNorma = Norma::factory()->create(['organisation_id' => $organisation->id]);
        $forNorma = Task::factory()->create([
            'place_id' => $norma->id,
            'taskable_type' => 'place',
            'taskable_id' => $norma->id,
        ])->refresh();
        $forOtherNorma = Task::factory()->create([
            'place_id' => $otherNorma->id,
            'taskable_type' => 'place',
            'taskable_id' => $otherNorma->id,
        ])->refresh();

        $responseNorma = [
            'id' => $forNorma->id,
            'title' => $forNorma->title,
            'due_at' => $forNorma->due_on?->toDateString(),
            'priority' => $forNorma->priority,
            'task_status' => $forNorma->task_status,
            'frequency' => $forNorma->frequency,
            'frequency_interval' => $forNorma->frequency_interval->value,
            'taskable_type' => $forNorma->taskable_type,
            'taskable_id' => $forNorma->taskable_id,
            'link' => route('my.tasks.tasks.show', ['task' => $forNorma->hash_id], false),
        ];
        $responseOtherNorma = [
            'id' => $forOtherNorma->id,
            'title' => $forOtherNorma->title,
            'due_at' => $forOtherNorma->due_on?->toDateString(),
            'priority' => $forOtherNorma->priority,
            'task_status' => $forOtherNorma->task_status,
            'frequency' => $forOtherNorma->frequency,
            'frequency_interval' => $forOtherNorma->frequency_interval->value,
            'taskable_type' => $forOtherNorma->taskable_type,
            'taskable_id' => $forOtherNorma->taskable_id,
            'link' => route('my.tasks.tasks.show', ['task' => $forOtherNorma->hash_id], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.tasks.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
                    $responseOtherNorma,
                ],
                'links' => [
                    'first' => route('api.v3.tasks.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'last' => route('api.v3.tasks.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.tasks.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = $norma->id;

        $route = route('api.v3.tasks.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
                ],
                'links' => [
                    'first' => route('api.v3.tasks.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.tasks.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.tasks.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 1,
                    'total' => 1,
                ],
            ]);

        $route = route('api.v3.tasks.show', [
            'organisation' => $organisation->id,
            'task' => $forOtherNorma->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherNorma,
            ]);
    }
}
