<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationLibryoControllerTest extends SettingsTestCase
{
    public function testIndexForbiddenForNonSuperAdmin(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.libryos.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();
    }

    public function testIndexAllOrgs(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.libryos.for.organisation.index', ['organisation' => $org->id]);

        $libryo = Libryo::factory()->create(['organisation_id' => $org->id]);
        $libryo2 = Libryo::factory()->create();
        $libryo3 = Libryo::factory()->create(['organisation_id' => $org->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route);
        $response->assertSee($libryo->title);
        $response->assertDontSee($libryo2->title);
        $response->assertSee($libryo3->title);
    }
}
