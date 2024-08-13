<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForUserLibryoControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $user1 = User::factory()->create();
        $route = route('my.settings.libryos.for.user.index', ['user' => $user1->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $libryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $libryo2 = Libryo::factory()->create();
        // libryo that the user is not a part of, but is part of the org
        $libryo3 = Libryo::factory()->create(['organisation_id' => $org->id]);
        $user1->libryos()->attach($libryo->id);
        $team = Team::factory()->create();
        $team->libryos()->attach($libryo2->id);
        $user1->teams()->attach($team->id);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.libryos.for.user.index', ['user' => $user1->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee($libryo->title);
        $response->assertDontSee($libryo2->title);
        $response->assertDontSee($libryo3->title);
    }

    public function testIndexAllOrgs(): void
    {
        $user1 = User::factory()->create();
        $route = route('my.settings.libryos.for.user.index', ['user' => $user1->id]);
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $libryo2 = Libryo::factory()->create();
        // libryo that the user is not a part of, but is part of the org
        $libryo3 = Libryo::factory()->create(['organisation_id' => $org->id]);
        $user1->libryos()->attach($libryo->id);
        $team = Team::factory()->create();
        $team->libryos()->attach($libryo2->id);
        $user1->teams()->attach($team->id);
        $user1->libryos()->attach($libryo2->id);

        $libryos = Libryo::factory(100)->create();

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route);
        $response->assertSee($libryo->title);
        $response->assertSee($libryo2->title);
        $response->assertDontSee($libryo3->title);

        $response = $this->get(route('my.settings.libryos.for.user.index', ['user' => $user1->id, 'page' => 2]));
        $response->assertSuccessful();
    }
}
