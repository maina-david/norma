<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Illuminate\Support\Str;
use Tests\Feature\Settings\SettingsTestCase;

class TeamControllerTest extends SettingsTestCase
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Team::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.teams';
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleLabels(): array
    {
        return ['Title'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleFields(): array
    {
        return ['title'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function createFormVisibleLabels(): array
    {
        return ['Title'];
    }

    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.teams.index'), 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $team = Team::factory()->create(['organisation_id' => $org->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.teams.index', ['activateOrgId' => $org->id]), 'get');

        $response->assertSee($org->title);
        $response->assertSee('teams');
        $response->assertSee($team->title);

        $response = $this->get(route('my.settings.teams.index', ['search' => Str::random(10)]));
        $response->assertDontSee($team->title);
        $response = $this->get(route('my.settings.teams.index', ['search' => $team->title]));
        $response->assertSee($team->title);
        $response = $this->get(route('my.settings.teams.index', ['search' => substr($team->title, 0, 4)]));
        $response->assertSee($team->title);
    }

    public function testIndexAllOrgs(): void
    {
        $user = $this->activateAllOrg();

        $team = Team::factory()->create();
        $user->teams()->attach($team->id);

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.teams.index'));
        $response->assertSuccessful();
        $response->assertSee('Teams');
        $response->assertSee('Users in team');

        $response->assertSee($team->title);
        $response->assertSeeSelector('//table//tr//*[1][contains(text(),"' . $team->title . '")]');
        $response->assertSeeSelector('//table//tr//*[2][contains(text(),"' . $team->users()->count() . '")]');
    }

    public function testIndexJson(): void
    {
        $user = $this->activateAllOrg();

        $team = Team::factory()->create();
        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.teams.index'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $team->id, 'title' => $team->title]]);
    }

    /**
     * @return void
     */
    public function testShow(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $route = route('my.settings.teams.show', ['team' => $team->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $user->organisations()->attach($org, ['is_admin' => true]);

        $routeActivate = route('my.settings.teams.show', ['team' => $team->id, 'activateOrgId' => $org->id]);
        $response = $this->assertCanAccessAfterOrgActivate($routeActivate, 'get');

        $response->assertSee($team->title);
        $response->assertSee('Users');
        $response->assertSee('Norma Streams');
        $response->assertSeeSelector('//header//div//span[contains(text(),"' . $team->title . '")]');
    }

    /**
     * @return void
     */
    public function testEdit(): void
    {
        [$user, $org, $resource, $route, $response] = $this->runEditTest(true);
        $response->assertDontSeeSelector('//label[text()[contains(.,"Organisation")]]');

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();
        $response->assertSeeSelector('//label[text()[contains(.,"Organisation")]]');
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        [$user, $org, $resource, $route, $response, $editRoute] = $this->runUpdateTest(true);

        $this->activateAllOrg();
        // teste can't update without organisation_id
        $updatedTitle = 'Updated title 2';
        $response = $this->put($route, ['title' => $updatedTitle]);
        $response->assertInvalid();

        $response = $this->put($route, ['title' => $updatedTitle, 'organisation_id' => $org->id]);
        $response->assertValid();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        [$user, $org, $resource, $route, $response] = $this->runCreateTest();
        $response->assertDontSeeSelector('//label[text()[contains(.,"Organisation")]]');

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();
        $response->assertSeeSelector('//label[text()[contains(.,"Organisation")]]');
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        [$user, $resource, $route, $org] = $this->createResourceAndAssertForbidden(
            static::resourceRoute() . '.store',
            'get',
            true
        );
        $createData = Team::factory()->for($org)->raw();

        $count = Team::count();
        $this->withActivatedOrg($org);
        $response = $this->followingRedirects()->post($route, $createData)->assertSuccessful();
        $this->assertGreaterThan($count, Team::count());

        $this->flushSession();
        $this->activateAllOrg();
        $newTitle = 'New Title';
        $response = $this->post($route, ['title' => $newTitle]);
        $response->assertInvalid();

        $response = $this->post($route, ['title' => $newTitle, 'organisation_id' => $org->id]);
        $response->assertValid();
    }

    /**
     * @return void
     */
    public function testDestroy(): void
    {
        $this->runDestroyTest(true);
    }
}
