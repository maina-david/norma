<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class ForUserNormaControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $user1 = User::factory()->create();
        $route = route('my.settings.normas.for.user.index', ['user' => $user1->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $norma = Norma::factory()->create(['organisation_id' => $org->id]);
        $norma2 = Norma::factory()->create();
        // norma that the user is not a part of, but is part of the org
        $norma3 = Norma::factory()->create(['organisation_id' => $org->id]);
        $user1->normas()->attach($norma->id);
        $team = Team::factory()->create();
        $team->normas()->attach($norma2->id);
        $user1->teams()->attach($team->id);

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.normas.for.user.index', ['user' => $user1->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the norma is part of that are in this org
        $response->assertSee($norma->title);
        $response->assertDontSee($norma2->title);
        $response->assertDontSee($norma3->title);
    }

    public function testIndexAllOrgs(): void
    {
        $user1 = User::factory()->create();
        $route = route('my.settings.normas.for.user.index', ['user' => $user1->id]);
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->create(['organisation_id' => $org->id]);
        $norma2 = Norma::factory()->create();
        // norma that the user is not a part of, but is part of the org
        $norma3 = Norma::factory()->create(['organisation_id' => $org->id]);
        $user1->normas()->attach($norma->id);
        $team = Team::factory()->create();
        $team->normas()->attach($norma2->id);
        $user1->teams()->attach($team->id);
        $user1->normas()->attach($norma2->id);

        $normas = Norma::factory(100)->create();

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the norma is part of
        $response = $this->get($route);
        $response->assertSee($norma->title);
        $response->assertSee($norma2->title);
        $response->assertDontSee($norma3->title);

        $response = $this->get(route('my.settings.normas.for.user.index', ['user' => $user1->id, 'page' => 2]));
        $response->assertSuccessful();
    }
}
