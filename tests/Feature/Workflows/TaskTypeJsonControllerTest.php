<?php

namespace Tests\Feature\Workflows;

use App\Models\Workflows\TaskType;
use Tests\TestCase;

class TaskTypeJsonControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testFetchingContextQuestions(): void
    {
        $types = TaskType::factory()->count(3)->create();

        $type = $types->first();

        $this->signIn();

        $route = route('collaborate.task-types.json.index', ['search' => $type->name]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJsonFragment([['id' => $type->id, 'title' => $type->name]]);

        $parents = TaskType::factory()->count(3)->create(['is_parent' => true]);
        $children = TaskType::factory()->count(3)->create(['is_parent' => false]);

        $response = $this->get(route('collaborate.task-types.json.index', ['parents' => true]))->assertSuccessful();

        $parents->each(fn ($parent) => $response->assertSee($parent->name));
        $children->each(fn ($child) => $response->assertDontSee($child->name));

        $response = $this->get(route('collaborate.task-types.json.index', ['children' => true]))->assertSuccessful();

        $children->each(fn ($child) => $response->assertSee($child->name));
        $parents->each(fn ($parent) => $response->assertDontSee($parent->name));
    }
}
