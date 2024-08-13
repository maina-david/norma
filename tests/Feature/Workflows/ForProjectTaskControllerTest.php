<?php

namespace Tests\Feature\Workflows;

use App\Models\Workflows\Project;
use App\Models\Workflows\Task;
use Tests\TestCase;

class ForProjectTaskControllerTest extends TestCase
{
    protected bool $collaborate = true;

    public function testItRendersTheIndexPage(): void
    {
        /** @var Project */
        $project = Project::factory()->create();

        /** @var Task */
        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);
        $task2 = Task::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.projects.tasks.index', ['project' => $project->id]))
            ->assertSuccessful()
            ->assertSee($task->title)
            ->assertDontSee($task2->title);
    }
}
