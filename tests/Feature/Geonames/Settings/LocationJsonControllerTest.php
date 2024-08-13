<?php

namespace Tests\Feature\Geonames\Settings;

use App\Models\Geonames\Location;
use Illuminate\Support\Str;
use Tests\Feature\Settings\SettingsTestCase;

class LocationJsonControllerTest extends SettingsTestCase
{
    public function testIndexJson(): void
    {
        $this->activateAllOrg();

        $locationGrandParent = Location::factory()->create();
        $locationParent = Location::factory()->create(['parent_id' => $locationGrandParent->id]);
        $location = Location::factory()->create(['parent_id' => $locationParent->id]);
        $location->ancestors()->attach($locationParent, ['depth' => 1]);
        $location->ancestors()->attach($locationGrandParent, ['depth' => 2]);

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.locations.json.index'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $location->id, 'title' => $location->title, 'details' => $location->load(['ancestors', 'ancestors.type', 'type'])->getBreadcrumbs()]]);

        $response = $this->get(route('my.settings.locations.json.index', ['search' => $location->title]), ['Accept' => 'application/json']);
        $response->assertJsonFragment([['id' => $location->id, 'title' => $location->title, 'details' => $location->load(['ancestors', 'ancestors.type', 'type'])->getBreadcrumbs()]]);
        $response = $this->get(route('my.settings.locations.json.index', ['search' => Str::random()]), ['Accept' => 'application/json']);
        $response->assertJsonMissing([['id' => $location->id, 'title' => $location->title, 'details' => $location->load(['ancestors', 'ancestors.type', 'type'])->getBreadcrumbs()]]);

        $response = $this->get(route('my.settings.locations.json.index', ['countries' => true]), ['Accept' => 'application/json']);
        $response->assertJsonFragment([['id' => $locationGrandParent->id, 'title' => $locationGrandParent->title, 'details' => $locationGrandParent->load(['ancestors', 'ancestors.type', 'type'])->getBreadcrumbs()]]);
    }
}
