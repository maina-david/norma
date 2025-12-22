<?php

namespace Tests\Feature\Actions\My;

use App\Models\Tasks\TaskProject;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Traits\HasDestroyTests;
use Tests\Feature\Traits\TestsVisibleFormLabels;

class ActionsProjectControllerTest extends MyTestCase
{
    use HasDestroyTests;
    use TestsVisibleFormLabels;

    public function testIndex(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.projects.index';

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id]);

        $this->validateActionsIsDisabled(route($routeName), $norma, $org);

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//td[text()[contains(.,"' . $project->title . '")]]');
        $response->assertSeeSelector('//td[text()[contains(.,"' . $project->description . '")]]');

        $response = $this->get(route($routeName, ['archived' => 'true']))->assertSuccessful();
        $response->assertDontSeeSelector('//td[text()[contains(.,"' . $project->title . '")]]');
        $response = $this->get(route($routeName, ['archived' => 'false']))->assertSuccessful();
        $response->assertSeeSelector('//td[text()[contains(.,"' . $project->title . '")]]');

        $response = $this->get(route($routeName, ['search' => substr($project->title, 0, 5)]))->assertSuccessful();
        $response->assertSeeSelector('//td[text()[contains(.,"' . $project->title . '")]]');

        $response = $this->get(route($routeName, ['search' => 'aiuhasdfliuahsdfliashdlfhsd']))->assertSuccessful();
        $response->assertDontSeeSelector('//td[text()[contains(.,"' . $project->title . '")]]');
    }

    public function testDestroy(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.projects.destroy';

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id]);

        $this->validateActionsIsDisabled(route($routeName, ['project' => $project->id]), $norma, $org, 'delete');

        $this->destroyAndTestData(TaskProject::class, route($routeName, ['project' => $project]));
    }

    public function testCreate(): void
    {
        $routeName = 'my.projects.create';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $this->validateActionsIsDisabled(route($routeName), $norma, $org);

        $response = $this->assertSeeVisibleFormLabels(
            [],
            route($routeName),
            ['Title', 'Description']
        );
    }

    public function testStore(): void
    {
        $routeName = 'my.projects.store';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $project = TaskProject::factory()->make();

        $this->validateActionsIsDisabled(route($routeName), $norma, $org, 'post', $project->toArray());

        $count = TaskProject::count();
        $response = $this->post(route($routeName), $project->toArray());
        $response->assertSessionDoesntHaveErrors();
        $this->assertGreaterThan($count, TaskProject::count());
    }

    public function testEdit(): void
    {
        $routeName = 'my.projects.edit';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id]);

        $this->validateActionsIsDisabled(route($routeName, ['project' => $project->id]), $norma, $org);

        $response = $this->assertSeeVisibleFormLabels(
            $project,
            route($routeName, ['project' => $project->hash_id]),
            ['Title', 'Description'],
            ['title'],
        );
    }

    public function testUpdate(): void
    {
        $routeName = 'my.projects.update';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id]);

        $this->validateActionsIsDisabled(route($routeName, ['project' => $project->id]), $norma, $org, 'put', $project->toArray());

        $project->title = 'New Title';
        $project->description = 'New Description';

        $response = $this->followingRedirects()->put(route($routeName, ['project' => $project->id]), $project->toArray())->assertSuccessful();

        $project->refresh();
        $this->assertTrue($project->title === 'New Title');
    }

    public function testArchive(): void
    {
        $routeName = 'my.projects.archive';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id]);

        $this->validateActionsIsDisabled(route($routeName, ['project' => $project->id]), $norma, $org, 'post');

        $this->assertFalse($project->archived);
        $response = $this->followingRedirects()->post(route($routeName, ['project' => $project->id]))->assertSuccessful();

        $project->refresh();
        $this->assertTrue($project->archived);
    }

    public function testUnarchive(): void
    {
        $routeName = 'my.projects.unarchive';
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $project = TaskProject::factory()->for($org)->create(['author_id' => $user->id, 'archived' => true]);

        $this->validateActionsIsDisabled(route($routeName, ['project' => $project->id]), $norma, $org, 'post');

        $this->assertTrue($project->archived);
        $response = $this->followingRedirects()->post(route($routeName, ['project' => $project->id]))->assertSuccessful();

        $project->refresh();
        $this->assertFalse($project->archived);
    }
}
