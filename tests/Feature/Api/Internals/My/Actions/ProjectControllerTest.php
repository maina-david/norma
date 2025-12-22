<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Models\Tasks\TaskProject;
use Tests\Feature\My\MyTestCase;

class ProjectControllerTest extends MyTestCase
{
    public function testListing(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $project = TaskProject::factory()->create(['organisation_id' => $org->id]);
        $others = TaskProject::factory()->count(3)->create();

        $this->getJson(route('api.my.actions.projects.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'id' => $project->id,
                        'title' => $project->title,
                    ],
                ],
            ]);
    }
}
