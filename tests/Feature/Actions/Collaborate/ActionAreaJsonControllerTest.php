<?php

namespace Tests\Feature\Actions\Collaborate;

use App\Models\Actions\ActionArea;
use Tests\TestCase;

class ActionAreaJsonControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testJsonIndex(): void
    {
        $area = ActionArea::factory()->create();

        $this->signIn();

        $route = route('collaborate.action-areas.index.json', ['search' => $area->title]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJson([['id' => $area->id, 'title' => $area->title]]);
    }
}
