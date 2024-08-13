<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForTeamLibryoControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $route = route('my.settings.libryos.for.team.index', ['team' => $team->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $libryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $libryo2 = Libryo::factory()->create();
        // libryo that the user is not a part of, but is part of the org
        $libryo3 = Libryo::factory()->create(['organisation_id' => $org->id]);
        $team->libryos()->attach([$libryo->id, $libryo2->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.libryos.for.team.index', ['team' => $team->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee($libryo->title);
        $response->assertDontSee($libryo2->title);
        $response->assertDontSee($libryo3->title);

        $route = route('my.settings.libryos.for.team.index', ['team' => $team->id, 'search' => $libryo->title]);
        $response = $this->get($route);
        $response->assertSee($libryo->title);

        $route = route('my.settings.libryos.for.team.index', ['team' => $team->id, 'deactivated' => 1]);
        $response = $this->get($route);
        $response->assertDontSee($libryo->title);
    }

    public function testIndexAllOrgs(): void
    {
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $route = route('my.settings.libryos.for.team.index', ['team' => $team->id]);

        $libryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $libryo2 = Libryo::factory()->create();
        // libryo that the team is not a part of, but is part of the org
        $libryo3 = Libryo::factory()->create(['organisation_id' => $org->id]);
        $team->libryos()->attach([$libryo->id, $libryo2->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route);
        $response->assertSee($libryo->title);
        $response->assertSee($libryo2->title);
        $response->assertDontSee($libryo3->title);
    }

    /**
     * @return void
     */
    public function testRemoveLibryosFromTeamAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.libryos.for.team.actions.organisation', ['organisation' => $org->id, 'team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $libryo1 = Libryo::factory()->for($org)->create();
        $libryo2 = Libryo::factory()->for($org)->create();
        $testTeam->libryos()->attach($libryo2);
        $libryo3 = Libryo::factory()->create();
        $testTeam->libryos()->attach($libryo3);

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $libryo2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('libryos');
        $this->assertFalse($testTeam->libryos->contains($libryo2));

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testTeam->libryos->contains($libryo3));
        $routeAll = route('my.settings.libryos.for.team.actions.all', ['team' => $testTeam->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $libryo3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('libryos');
        $this->assertFalse($testTeam->libryos->contains($libryo3));
    }

    /**
     * @return void
     */
    public function testAddLibryosAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.libryos.for.team.add', ['team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $user->organisations()->attach($org, ['is_admin' => true]);

        $libryo = Libryo::factory()->for($org)->create();
        $libryo2 = Libryo::factory()->create();

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'libryos' => [
                    $libryo->id,
                    $libryo2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->libryos->contains($libryo));
        // $libryo2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testTeam->libryos->contains($libryo2));
    }

    /**
     * @return void
     */
    public function testAddLibryosActionAllOrg(): void
    {
        $testTeam = Team::factory()->create();
        $route = route('my.settings.libryos.for.team.add', ['team' => $testTeam->id]);
        $libryoNotInOrg = Libryo::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'libryos' => [
                    $libryoNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->libryos->contains($libryoNotInOrg));
    }
}
