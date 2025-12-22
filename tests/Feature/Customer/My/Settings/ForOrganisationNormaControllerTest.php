<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationNormaControllerTest extends SettingsTestCase
{
    public function testIndexForbiddenForNonSuperAdmin(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.normas.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();
    }

    public function testIndexAllOrgs(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.normas.for.organisation.index', ['organisation' => $org->id]);

        $norma = Norma::factory()->create(['organisation_id' => $org->id]);
        $norma2 = Norma::factory()->create();
        $norma3 = Norma::factory()->create(['organisation_id' => $org->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the norma is part of
        $response = $this->get($route);
        $response->assertSee($norma->title);
        $response->assertDontSee($norma2->title);
        $response->assertSee($norma3->title);
    }
}
