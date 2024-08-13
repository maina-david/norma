<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibraryLibryoControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $library = Library::factory()->create();
        $org = Organisation::factory()->create();
        Libryo::factory()->for($library)->for($org)->create();
        $route = route('my.settings.libryos.for.library.index', ['library' => $library->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $libryo1 = Libryo::factory()->for($org)->for($library)->create();
        $libryo2 = Libryo::factory()->for($library)->create();

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.libryos.for.library.index', ['library' => $library->id, 'activateOrgId' => $org->id]), 'get');

        // when viewing in single organisations mode, you should only see the teams the libryo is part of that are in this org
        $response->assertSee($libryo1->title);
        $response->assertDontSee($libryo2->title);
    }

    /**
     * @return void
     */
    public function testIndexAllOrgs(): void
    {
        $this->signIn($this->mySuperUser());
        $library = Library::factory()->create();
        $route = route('my.settings.libryos.for.library.index', ['library' => $library->id]);

        $libryo1 = Libryo::factory()->for($library)->create();
        $libryo2 = Libryo::factory()->for($library)->create();

        $response = $this->withAllOrgMode()->get($route);
        $response->assertSee($libryo1->title);
    }
}
