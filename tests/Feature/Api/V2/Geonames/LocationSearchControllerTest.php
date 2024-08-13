<?php

namespace Tests\Feature\Api\V2\Geonames;

use App\Models\Auth\User;
use App\Models\Geonames\Location;
use Laravel\Passport\Passport;
use Tests\Feature\Api\ApiTestCase;

class LocationSearchControllerTest extends ApiTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheCorrectItems(): void
    {
        Passport::actingAs(User::factory()->create());
        /** @var Location $country */
        $country = Location::factory()->create();
        $country->location_country_id = $country->id;
        $country->save();

        $level2 = Location::factory()->create([
            'parent_id' => $country->id,
            'location_country_id' => $country->id,
            'level' => 2,
        ]);

        $level2b = Location::factory()->create([
            'parent_id' => $country->id,
            'location_country_id' => $country->id,
            'level' => 2,
        ]);

        $level3 = Location::factory()->create([
            'parent_id' => $level2->id,
            'location_country_id' => $country->id,
            'level' => 3,
        ]);

        $level3b = Location::factory()->create([
            'title' => $level3->title,
            'parent_id' => $level2b->id,
            'location_country_id' => $country->id,
            'level' => 3,
        ]);

        $this->json('GET', route('api.v2.locations.find-by-name'))
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('country')
            ->assertJsonValidationErrorFor('level')
            ->assertJsonValidationErrorFor('name');

        $this->json('GET', route('api.v2.locations.find-by-name', [
            'country' => $country->slug,
            'name' => $level2->title,
            'level' => 2,
        ]))->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $level2->id,
                        'parent_id' => $country->id,
                        'location_type_id' => $level2->location_type_id,
                        'location_country_id' => $country->id,
                        'title' => $level2->title,
                        'slug' => $level2->slug,
                        'level' => 2,
                        'flag' => $level2->flag,
                        'location_code' => $level2->location_code,
                    ],
                ],
            ]);

        $this->json('GET', route('api.v2.locations.find-by-name', [
            'country' => $country->slug,
            'name' => $level3->title,
            'level' => 3,
        ]))->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $level3->id,
                        'parent_id' => $level2->id,
                        'location_type_id' => $level3->location_type_id,
                        'location_country_id' => $country->id,
                        'title' => $level3->title,
                        'slug' => $level3->slug,
                        'level' => 3,
                        'flag' => $level3->flag,
                        'location_code' => $level3->location_code,
                    ],
                    [
                        'id' => $level3b->id,
                        'parent_id' => $level2b->id,
                        'location_type_id' => $level3b->location_type_id,
                        'location_country_id' => $country->id,
                        'title' => $level3b->title,
                        'slug' => $level3b->slug,
                        'level' => 3,
                        'flag' => $level3b->flag,
                        'location_code' => $level3b->location_code,
                    ],
                ],
            ]);

        $this->json('GET', route('api.v2.locations.find-by-name', [
            'country' => $country->slug,
            'name' => $level3->title,
            'level' => 3,
            'level_2' => $level2->title,
        ]))->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $level3->id,
                        'parent_id' => $level2->id,
                        'location_type_id' => $level3->location_type_id,
                        'location_country_id' => $country->id,
                        'title' => $level3->title,
                        'slug' => $level3->slug,
                        'level' => 3,
                        'flag' => $level3->flag,
                        'location_code' => $level3->location_code,
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }
}
