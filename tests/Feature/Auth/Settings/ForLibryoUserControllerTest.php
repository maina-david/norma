<?php

namespace Tests\Feature\Auth\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibryoUserControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $libryo = Libryo::factory()->create();
        $route = route('my.settings.users.for.libryo.index', ['libryo' => $libryo->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        [$org, $team, $libryo, $user1, $user2, $user3] = $this->prepIndexData($libryo);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $libryo->update(['organisation_id' => $org->id]);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.users.for.libryo.index', ['libryo' => $libryo->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the user is part of that are in this org
        $response->assertSee($user1->fullName);
        $response->assertSee($user2->fullName);
        $response->assertDontSee($user3->fullName);
    }

    public function testIndexAllOrgs(): void
    {
        $libryo = Libryo::factory()->create();
        $route = route('my.settings.users.for.libryo.index', ['libryo' => $libryo->id]);
        $org = Organisation::factory()->create();

        [$org, $team, $libryo, $user1, $user2, $user3] = $this->prepIndexData($libryo);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get($route);
        $response->assertSee($user3->fullName);
    }

    private function prepIndexData($libryo): array
    {
        $org = Organisation::factory()->create();
        $team = Team::factory()->for($org)->create();
        $libryo->teams()->attach($team->id);
        $user1 = User::factory()->create(); // user associated via team
        $team->users()->attach($user1->id);
        $user2 = User::factory()->create(); // user associated via Libryo directly
        $libryo->users()->attach($user2->id);
        $user3 = User::factory()->create(); // user associated via team, but not part of org
        $team->users()->attach($user3->id);
        $org->users()->attach([$user1->id, $user2->id]);

        return [$org, $team, $libryo, $user1, $user2, $user3];
    }
}
