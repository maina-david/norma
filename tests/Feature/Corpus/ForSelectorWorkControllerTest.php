<?php

namespace Tests\Feature\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use Tests\Feature\Settings\SettingsTestCase;

class ForSelectorWorkControllerTest extends SettingsTestCase
{
    public function testIndex(): void
    {
        $routeName = 'my.settings.corpus.works.for.selector.index';
        $work = Work::factory()->has(Reference::factory())->create();

        $location = Location::factory()->create();
        $work->references->first()->locations()->attach($location);

        $route = route($routeName, ['work' => $work->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser($user));

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($work->title);
    }
}
