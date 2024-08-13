<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForUserTeamControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndexForUser(): void
    {
        $user = $this->signIn();
        $user = $this->assertForbiddenForNonAdmin(route('my.settings.teams.for.user.index', ['user' => $user->id]), 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        // team that the user is not a part of, but is part of the org
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);
        $user->teams()->attach([$team->id, $team2->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.teams.for.user.index', ['user' => $user->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the user is part of that are in this org
        $response->assertSee($team->title);
        $response->assertDontSee($team2->title);
        $response->assertDontSee($team3->title);

        $this->mySuperUser($user);
        $user->flushComputedPermissions();
        $response = $this->post(route('my.settings.organisations.activate.all'));
        $response->assertRedirect();

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.teams.for.user.index', ['user' => $user->id]));
        $response->assertSee($team->title);
        $response->assertDontSee($team3->title);
    }

    /**
     * @return void
     */
    public function testRemoveTeamFromUserAction(): void
    {
        $org = Organisation::factory()->create();
        $testUser = User::factory()->create();
        $route = route('my.settings.teams.for.user.actions.organisation', ['organisation' => $org->id, 'user' => $testUser->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $team2 = Team::factory()->for($org)->create();
        $testUser->teams()->attach($team2);
        $team3 = Team::factory()->create();
        $testUser->teams()->attach($team3);

        $user->organisations()->attach($org, ['is_admin' => true]);

        $routeActivate = route('my.settings.teams.for.user.actions.organisation', ['activateOrgId' => $org->id, 'organisation' => $org->id, 'user' => $testUser->id]);
        $this->get($routeActivate);

        // test posting without 'action'
        $response = $this->post($route);
        $response->assertStatus(422);

        // test posting with 'action', but no selected items
        $response = $this->post($route, ['action' => 'remove']);
        $response->assertStatus(422);
        $response->assertSessionHas('flash.message', 'No items selected');

        // test posting with 'action', and with one item
        $response = $this->post($route, [
            'action' => 'remove',
            'actions-checkbox-' . $team2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testUser->load('teams');
        $this->assertFalse($testUser->teams->contains($team2));

        // test trying to remove a team that's not in the org
        $response = $this->post($route, [
            'action' => 'remove',
            'actions-checkbox-' . $team3->id => 'on',
        ]);
        $response->assertStatus(403);

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testUser->teams->contains($team3));
        $routeAll = route('my.settings.teams.for.user.actions.all', ['user' => $testUser->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove',
            'actions-checkbox-' . $team3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testUser->load('teams');
        $this->assertFalse($testUser->teams->contains($team3));
    }

    /**
     * @return void
     */
    public function testAddTeamsAction(): void
    {
        $org = Organisation::factory()->create();
        $testUser = User::factory()->create();
        $route = route('my.settings.teams.for.user.add', ['user' => $testUser->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $team = Team::factory()->for($org)->create();
        $team2 = Team::factory()->create();

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'teams' => [
                    $team->id,
                    $team2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testUser->teams->contains($team));
        // $team2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testUser->teams->contains($team2));
    }

    /**
     * @return void
     */
    public function testAddTeamsActionAllOrg(): void
    {
        $testUser = User::factory()->create();
        $route = route('my.settings.teams.for.user.add', ['user' => $testUser->id]);
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
        $this->assertTrue($testUser->teams->contains($teamNotInOrg));
    }
}
