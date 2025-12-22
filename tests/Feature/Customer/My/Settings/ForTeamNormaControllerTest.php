<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForTeamNormaControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $route = route('my.settings.normas.for.team.index', ['team' => $team->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $norma = Norma::factory()->create(['organisation_id' => $org->id]);
        $norma2 = Norma::factory()->create();
        // norma that the user is not a part of, but is part of the org
        $norma3 = Norma::factory()->create(['organisation_id' => $org->id]);
        $team->normas()->attach([$norma->id, $norma2->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.normas.for.team.index', ['team' => $team->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the norma is part of that are in this org
        $response->assertSee($norma->title);
        $response->assertDontSee($norma2->title);
        $response->assertDontSee($norma3->title);

        $route = route('my.settings.normas.for.team.index', ['team' => $team->id, 'search' => $norma->title]);
        $response = $this->get($route);
        $response->assertSee($norma->title);

        $route = route('my.settings.normas.for.team.index', ['team' => $team->id, 'deactivated' => 1]);
        $response = $this->get($route);
        $response->assertDontSee($norma->title);
    }

    public function testIndexAllOrgs(): void
    {
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $route = route('my.settings.normas.for.team.index', ['team' => $team->id]);

        $norma = Norma::factory()->create(['organisation_id' => $org->id]);
        $norma2 = Norma::factory()->create();
        // norma that the team is not a part of, but is part of the org
        $norma3 = Norma::factory()->create(['organisation_id' => $org->id]);
        $team->normas()->attach([$norma->id, $norma2->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the norma is part of
        $response = $this->get($route);
        $response->assertSee($norma->title);
        $response->assertSee($norma2->title);
        $response->assertDontSee($norma3->title);
    }

    /**
     * @return void
     */
    public function testRemoveNormasFromTeamAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.normas.for.team.actions.organisation', ['organisation' => $org->id, 'team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $norma1 = Norma::factory()->for($org)->create();
        $norma2 = Norma::factory()->for($org)->create();
        $testTeam->normas()->attach($norma2);
        $norma3 = Norma::factory()->create();
        $testTeam->normas()->attach($norma3);

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $norma2->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('normas');
        $this->assertFalse($testTeam->normas->contains($norma2));

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $this->assertTrue($testTeam->normas->contains($norma3));
        $routeAll = route('my.settings.normas.for.team.actions.all', ['team' => $testTeam->id]);
        $response = $this->post($routeAll, [
            'action' => 'remove_from_team',
            'actions-checkbox-' . $norma3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $testTeam->load('normas');
        $this->assertFalse($testTeam->normas->contains($norma3));
    }

    /**
     * @return void
     */
    public function testAddNormasAction(): void
    {
        $org = Organisation::factory()->create();
        $testTeam = Team::factory()->for($org)->create();
        $route = route('my.settings.normas.for.team.add', ['team' => $testTeam->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $user->organisations()->attach($org, ['is_admin' => true]);

        $norma = Norma::factory()->for($org)->create();
        $norma2 = Norma::factory()->create();

        $response = $this->withActivatedOrg($org)
            ->post($route, [
                'normas' => [
                    $norma->id,
                    $norma2->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->normas->contains($norma));
        // $norma2 is not in the org, so should not get attached in single org mode
        $this->assertFalse($testTeam->normas->contains($norma2));
    }

    /**
     * @return void
     */
    public function testAddNormasActionAllOrg(): void
    {
        $testTeam = Team::factory()->create();
        $route = route('my.settings.normas.for.team.add', ['team' => $testTeam->id]);
        $normaNotInOrg = Norma::factory()->create();

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);
        $response = $this->withAllOrgMode()
            ->post($route, [
                'normas' => [
                    $normaNotInOrg->id,
                ],
            ]);
        $response->assertRedirect();
        $this->assertTrue($testTeam->normas->contains($normaNotInOrg));
    }
}
