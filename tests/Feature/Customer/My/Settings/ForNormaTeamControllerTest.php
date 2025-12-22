<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForNormaTeamControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndexForUser(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.teams.for.norma.index', ['norma' => $norma->id]), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        // team that the user is not a part of, but is part of the org
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);
        $norma->teams()->attach([$team->id, $team2->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.teams.for.norma.index', ['norma' => $norma->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the norma is part of that are in this org
        $response->assertSee($team->title);
        $response->assertDontSee($team2->title);
        $response->assertDontSee($team3->title);
    }

    public function testIndexAllOrgs(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.teams.for.norma.index', ['norma' => $norma->id]);
        $org = Organisation::factory()->create();
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        // team that the user is not a part of, but is part of the org
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);
        $norma->teams()->attach([$team->id, $team2->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the norma is part of
        $response = $this->get($route);
        $response->assertSee($team->title);
        $response->assertSee($team2->title);
        $response->assertDontSee($team3->title);
    }

    /**
     * @return void
     */
    public function testRemoveTeamsFromNormaAction(): void
    {
        $org = Organisation::factory()->create();
        $testNorma = Norma::factory()->for($org)->create();
        $route = route('my.settings.teams.for.norma.actions.organisation', ['organisation' => $org->id, 'norma' => $testNorma->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $team1 = Team::factory()->for($org)->create();
        $team2 = Team::factory()->for($org)->create();
        $testNorma->teams()->attach($team2);
        $team3 = Team::factory()->create();
        $testNorma->teams()->attach($team3);
        $team2->users()->attach($user);
        $testNorma->users()->attach($user, ['via_teams' => true]);

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_from_norma',
            'actions-checkbox-' . $team2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testNorma->load('teams');
        $this->assertFalse($testNorma->teams->contains($team2));

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testNorma->teams->contains($team3));
        $routeAll = route('my.settings.teams.for.norma.actions.all', ['norma' => $testNorma->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove_from_norma',
            'actions-checkbox-' . $team3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testNorma->load('teams');
        $this->assertFalse($testNorma->teams->contains($team3));
        // test that the place_user cache was cleared
        $this->assertFalse($testNorma->refresh()->users->contains($user));
    }

    /**
     * @return void
     */
    public function testAddTeamsAction(): void
    {
        $org = Organisation::factory()->create();
        $testNorma = Norma::factory()->for($org)->create();
        $route = route('my.settings.teams.for.norma.add', ['norma' => $testNorma->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $user->organisations()->attach($org, ['is_admin' => true]);

        $team = Team::factory()->for($org)->create();
        $team2 = Team::factory()->create();
        $team->users()->attach($user);

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'teams' => [
                    $team->id,
                    $team2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testNorma->teams->contains($team));
        // $team2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testNorma->teams->contains($team2));
        // test that the place_user cache has the norma added
        $this->assertTrue($testNorma->refresh()->users->contains($user));
    }

    /**
     * @return void
     */
    public function testAddTeamsActionAllOrg(): void
    {
        $testNorma = Norma::factory()->create();
        $route = route('my.settings.teams.for.norma.add', ['norma' => $testNorma->id]);
        $teamNotInOrg = Team::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'teams' => [
                    $teamNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testNorma->teams->contains($teamNotInOrg));
    }
}
