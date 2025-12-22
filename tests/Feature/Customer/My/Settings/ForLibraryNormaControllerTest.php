<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibraryNormaControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $library = Library::factory()->create();
        $org = Organisation::factory()->create();
        Norma::factory()->for($library)->for($org)->create();
        $route = route('my.settings.normas.for.library.index', ['library' => $library->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $norma1 = Norma::factory()->for($org)->for($library)->create();
        $norma2 = Norma::factory()->for($library)->create();

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.normas.for.library.index', ['library' => $library->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the norma is part of that are in this org
        $response->assertSee($norma1->title);
        $response->assertDontSee($norma2->title);
    }

    /**
     * @return void
     */
    public function testIndexAllOrgs(): void
    {
        $this->signIn($this->mySuperUser());
        $library = Library::factory()->create();
        $route = route('my.settings.normas.for.library.index', ['library' => $library->id]);

        $norma1 = Norma::factory()->for($library)->create();
        $norma2 = Norma::factory()->for($library)->create();

        $response = $this->withAllOrgMode()->get($route);
        $response->assertSee($norma1->title);
    }
}
