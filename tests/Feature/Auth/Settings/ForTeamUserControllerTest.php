<?php

namespace Tests\Feature\Auth\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForTeamUserControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $team = Team::factory()->create();
        $route = route('my.settings.users.for.team.index', ['team' => $team->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        [$org, $user1, $user2, $user3] = $this->prepIndexData($team);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $team->update(['organisation_id' => $org->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.users.for.team.index', ['team' => $team->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the user is part of that are in this org
        $response->assertSee($user1->fullName);
        $response->assertSee($user2->fullName);
        $response->assertDontSee($user3->fullName);
    }

    public function testIndexAllOrgs(): void
    {
        $team = Team::factory()->create();
        $route = route('my.settings.users.for.team.index', ['team' => $team->id]);
        $org = Organisation::factory()->create();

        [$org, $user1, $user2, $user3] = $this->prepIndexData($team);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get($route);
        $response->assertSee($user1->fullName);
        $response->assertSee($user3->fullName);
    }

    private function prepIndexData($team): array
    {
        $org = Organisation::factory()->create();
        $user1 = User::factory()->create(); // user associated via team
        $team->users()->attach($user1->id);
        $user2 = User::factory()->create(); // user associated via Libryo directly
        $team->users()->attach($user2->id);
        $user3 = User::factory()->create(); // user associated via team, but not part of org
        $team->users()->attach($user3->id);
        $org->users()->attach([$user1->id, $user2->id]);

        return [$org, $user1, $user2, $user3];
    }

    /**
     * @return void
     */
    public function testRemoveUserFromTeamAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.users.for.team.actions.organisation', ['organisation' => $org->id, 'team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $testTeam->users()->attach($user2);
        $org->users()->attach([$user1->id, $user2->id]);
        $user3 = User::factory()->create();
        $testTeam->users()->attach($user3);

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting without 'action'
        $response = $this->post($route);
        $response->assertStatus(422);

        // test posting with 'action', but no selected items
        $response = $this->post($route, ['action' => 'remove_from_team']);
        $response->assertStatus(422);
        $response->assertSessionHas('flash.message', 'No items selected');

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $user2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('users');
        $this->assertFalse($testTeam->users->contains($user2));

        // test trying to remove a user that's not in the org
        $response = $this->post($route, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $user3->id => 'on',
        ]);
        $response->assertStatus(403);

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testTeam->users->contains($user3));
        $routeAll = route('my.settings.users.for.team.actions.all', ['team' => $testTeam->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $user3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('users');
        $this->assertFalse($testTeam->users->contains($user3));
    }

    /**
     * @return void
     */
    public function testAddUsersAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.users.for.team.add', ['team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $user = User::factory()->create();
        $org->users()->attach($user);
        $user2 = User::factory()->create();

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'users' => [
                    $user->id,
                    $user2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->users->contains($user));
        // $user2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testTeam->users->contains($user2));
    }

    /**
     * @return void
     */
    public function testAddUsersActionAllOrg(): void
    {
        $testTeam = Team::factory()->create();
        $route = route('my.settings.users.for.team.add', ['team' => $testTeam->id]);
        $userNotInOrg = User::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'users' => [
                    $userNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->users->contains($userNotInOrg));
    }
}
