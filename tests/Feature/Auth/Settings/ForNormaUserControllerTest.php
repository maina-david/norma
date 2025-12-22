<?php

namespace Tests\Feature\Auth\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForNormaUserControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $norma = Norma::factory()->create();
        $route = route('my.settings.users.for.norma.index', ['norma' => $norma->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        [$org, $team, $norma, $user1, $user2, $user3] = $this->prepIndexData($norma);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $norma->update(['organisation_id' => $org->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.users.for.norma.index', ['norma' => $norma->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the user is part of that are in this org
        $response->assertSee($user1->fullName);
        $response->assertSee($user2->fullName);
        $response->assertDontSee($user3->fullName);
    }

    public function testIndexAllOrgs(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.users.for.norma.index', ['norma' => $norma->id]);
        $org = Organisation::factory()->create();

        [$org, $team, $norma, $user1, $user2, $user3] = $this->prepIndexData($norma);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get($route);
        $response->assertSee($user3->fullName);
    }

    private function prepIndexData($norma): array
    {
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $norma->teams()->attach($team->id);
        $user1 = User::factory()->create(); // user associated via team
        $team->users()->attach($user1->id);
        $user2 = User::factory()->create(); // user associated via Norma directly
        $norma->users()->attach($user2->id);
        $user3 = User::factory()->create(); // user associated via team, but not part of org
        $team->users()->attach($user3->id);
        $org->users()->attach([$user1->id, $user2->id]);

        return [$org, $team, $norma, $user1, $user2, $user3];
    }
}
