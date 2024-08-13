<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Models\Collaborators\Group;
use Tests\TestCase;

class GroupJsonControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testFetchingItems(): void
    {
        $groups = Group::factory()->count(3)->create();

        $group = $groups->first();

        $this->signIn();

        $route = route('collaborate.groups.json.index', ['search' => $group->fname]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJsonFragment([['id' => $group->id, 'title' => $group->title]]);
    }
}
