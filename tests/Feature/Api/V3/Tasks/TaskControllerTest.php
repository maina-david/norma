<?php

namespace Tests\Feature\Api\V3\Tasks;

use App\Models\Customer\Libryo;
use App\Models\Tasks\Task;
use Tests\Feature\Api\V3\ApiV3TestCase;

class TaskControllerTest extends ApiV3TestCase
{
    public function testTasksForOrganisation(): void
    {
        /** @var Libryo $libryo */
        [$libryo, $organisation, $work, $updatesCollection, $domain, $tag, $childWork] = $this->initCompiledStream();

        $otherLibryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);
        $forLibryo = Task::factory()->create([
            'place_id' => $libryo->id,
            'taskable_type' => 'place',
            'taskable_id' => $libryo->id,
        ])->refresh();
        $forOtherLibryo = Task::factory()->create([
            'place_id' => $otherLibryo->id,
            'taskable_type' => 'place',
            'taskable_id' => $otherLibryo->id,
        ])->refresh();

        $responseLibryo = [
            'id' => $forLibryo->id,
            'title' => $forLibryo->title,
            'due_at' => $forLibryo->due_on?->toDateString(),
            'priority' => $forLibryo->priority,
            'task_status' => $forLibryo->task_status,
            'frequency' => $forLibryo->frequency,
            'frequency_interval' => $forLibryo->frequency_interval->value,
            'taskable_type' => $forLibryo->taskable_type,
            'taskable_id' => $forLibryo->taskable_id,
            'link' => route('my.tasks.tasks.show', ['task' => $forLibryo->hash_id], false),
        ];
        $responseOtherLibryo = [
            'id' => $forOtherLibryo->id,
            'title' => $forOtherLibryo->title,
            'due_at' => $forOtherLibryo->due_on?->toDateString(),
            'priority' => $forOtherLibryo->priority,
            'task_status' => $forOtherLibryo->task_status,
            'frequency' => $forOtherLibryo->frequency,
            'frequency_interval' => $forOtherLibryo->frequency_interval->value,
            'taskable_type' => $forOtherLibryo->taskable_type,
            'taskable_id' => $forOtherLibryo->taskable_id,
            'link' => route('my.tasks.tasks.show', ['task' => $forOtherLibryo->hash_id], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.tasks.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
                    $responseOtherLibryo,
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

        $streams = $libryo->id;

        $route = route('api.v3.tasks.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
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
            'task' => $forOtherLibryo->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherLibryo,
            ]);
    }
}
