<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibryoTeamControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndexForUser(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id]), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        // team that the user is not a part of, but is part of the org
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);
        $libryo->teams()->attach([$team->id, $team2->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee($team->title);
        $response->assertDontSee($team2->title);
        $response->assertDontSee($team3->title);
    }

    public function testIndexAllOrgs(): void
    {
        $libryo = Libryo::factory()->create();
        $route = route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id]);
        $org = Organisation::factory()->create();
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        // team that the user is not a part of, but is part of the org
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);
        $libryo->teams()->attach([$team->id, $team2->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route);
        $response->assertSee($team->title);
        $response->assertSee($team2->title);
        $response->assertDontSee($team3->title);
    }

    /**
     * @return void
     */
    public function testRemoveTeamsFromLibryoAction(): void
    {
        $org = Organisation::factory()->create();
        $testLibryo = Libryo::factory()->for($org)->create();
        $route = route('my.settings.teams.for.libryo.actions.organisation', ['organisation' => $org->id, 'libryo' => $testLibryo->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $team1 = Team::factory()->for($org)->create();
        $team2 = Team::factory()->for($org)->create();
        $testLibryo->teams()->attach($team2);
        $team3 = Team::factory()->create();
        $testLibryo->teams()->attach($team3);
        $team2->users()->attach($user);
        $testLibryo->users()->attach($user, ['via_teams' => true]);

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_from_libryo',
            'actions-checkbox-' . $team2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testLibryo->load('teams');
        $this->assertFalse($testLibryo->teams->contains($team2));

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testLibryo->teams->contains($team3));
        $routeAll = route('my.settings.teams.for.libryo.actions.all', ['libryo' => $testLibryo->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove_from_libryo',
            'actions-checkbox-' . $team3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testLibryo->load('teams');
        $this->assertFalse($testLibryo->teams->contains($team3));
        // test that the place_user cache was cleared
        $this->assertFalse($testLibryo->refresh()->users->contains($user));
    }

    /**
     * @return void
     */
    public function testAddTeamsAction(): void
    {
        $org = Organisation::factory()->create();
        $testLibryo = Libryo::factory()->for($org)->create();
        $route = route('my.settings.teams.for.libryo.add', ['libryo' => $testLibryo->id]);
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
        $this->assertTrue($testLibryo->teams->contains($team));
        // $team2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testLibryo->teams->contains($team2));
        // test that the place_user cache has the libryo added
        $this->assertTrue($testLibryo->refresh()->users->contains($user));
    }

    /**
     * @return void
     */
    public function testAddTeamsActionAllOrg(): void
    {
        $testLibryo = Libryo::factory()->create();
        $route = route('my.settings.teams.for.libryo.add', ['libryo' => $testLibryo->id]);
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
        $this->assertTrue($testLibryo->teams->contains($teamNotInOrg));
    }
}
