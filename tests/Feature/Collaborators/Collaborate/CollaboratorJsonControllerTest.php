<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Models\Collaborators\Collaborator;
use Tests\TestCase;

class CollaboratorJsonControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testFetchingItems(): void
    {
        $collaborators = Collaborator::factory()->count(3)->create();

        $collaborator = $collaborators->first();

        $this->signIn();

        $route = route('collaborate.collaborators.json.index', ['search' => $collaborator->fname]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJsonFragment([['id' => $collaborator->id, 'title' => $collaborator->full_name]]);
    }
}
