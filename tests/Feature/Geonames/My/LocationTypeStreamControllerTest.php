<?php

namespace Tests\Feature\Geonames\My;

use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class LocationTypeStreamControllerTest extends MyTestCase
{
    public function testSearchSuggest(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($libryo);

        $routeName = 'my.geonames.location-types.search-suggest';
        $title = __('corpus.location_type.' . $requirementsCollection->locationType->adjective_key);
        // test without a search query
        $this->get(route($routeName, ['key' => 'reference-suggest']))
            ->assertSuccessful()
            ->assertDontSee($title);

        $this->activateAllStreams($user);

        $this->get(route($routeName, ['key' => 'reference-suggest', 'search' => substr($title, 0, 3), 'link' => 1]))
            ->assertSuccessful()
            ->assertSee($title);

        $this->get(route($routeName, ['key' => 'reference-suggest', 'search' => Str::random(), 'link' => 1]))
            ->assertSuccessful()
            ->assertDontSee($title);

        $this->deleteCompiledStream();
    }
}
