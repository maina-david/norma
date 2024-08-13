<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationTeamControllerTest extends SettingsTestCase
{
    public function testIndexForbiddenForNonSuperAdmin(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.teams.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();
    }

    public function testIndexAllOrgs(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.teams.for.organisation.index', ['organisation' => $org->id]);

        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $team2 = Team::factory()->create();
        $team3 = Team::factory()->create(['organisation_id' => $org->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route);
        $response->assertSee($team->title);
        $response->assertDontSee($team2->title);
        $response->assertSee($team3->title);
    }
}
