<?php

namespace Tests\Feature\Corpus\Settings;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use Tests\Feature\Settings\SettingsTestCase;

class ForManualCompilationWorkControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.compilation.works.for.manual.compilation';
        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get(route($routeName));
        $response->assertForbidden();

        $this->signIn($this->mySuperUser());

        $work = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $location = Location::factory()->create();
        $location->update(['location_country_id' => $location->id]);
        $reference->locations()->attach($location);
        $work2 = Work::factory()->create();

        $response = $this->get(route($routeName));
        $response->assertSee($work->title);
        $response->assertSee($work2->title);
        $response->assertSee($location->title);

        $response = $this->get(route($routeName, ['search' => $work->title]));
        $response->assertSee($work->title);
        $response->assertDontSee($work2->title);

        $response = $this->get(route($routeName, ['countries' => [$location->id]]));
        $response->assertSee($work->title);
        $response->assertDontSee($work2->title);
    }
}
