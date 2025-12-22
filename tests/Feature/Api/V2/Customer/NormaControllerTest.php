<?php

namespace Tests\Feature\Api\V2\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Api\ApiTestCase;

class NormaControllerTest extends ApiTestCase
{
    public function testIndex(): void
    {
        $normas = Norma::factory(2)->create();
        $normaUnattached = Norma::factory()->create();
        $user = User::factory()->create();
        $user->normas()->attach($normas);

        $routeName = 'api.v2.normas.index';
        $route = route($routeName);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        foreach ($normas as $norma) {
            $items[] = [
                'id' => $norma->id,
                'title' => $norma->title,
                'place_type_id' => $norma->place_type_id,
                'address' => $norma->address,
                'geo_lat' => $norma->geo_lat,
                'geo_lng' => $norma->geo_lng,
                'location_id' => $norma->location_id,
                'organisation_id' => $norma->organisation_id,
                'deactivated' => $norma->deactivated,
                'description' => $norma->description,
                'compilation_in_progress' => $norma->compilation_in_progress,
                'created_at' => $norma->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $norma->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        $response->assertJson([
            'data' => $items,
        ], true);
        $response->assertJsonMissing(['data' => ['id' => $normaUnattached->id]]);

        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'per_page' => 1,
            'to' => 1,
            'total' => 2,
        ];
        $links = [
            'first' => route($routeName, ['perPage' => 1, 'page' => 1]),
            'last' => route($routeName, ['perPage' => 1, 'page' => 2]),
            'prev' => null,
            'next' => route($routeName, ['perPage' => 1, 'page' => 2]),
        ];
        $route = route($routeName, ['perPage' => 1, 'page' => 1]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[0]], 'meta' => $meta, 'links' => $links]);
        $response->assertJsonMissing(['data' => [$items[1]]]);
    }

    /**
     * We'll use the NormaController to test all the API filters.
     *
     * @return void
     */
    public function testIndexFilters(): void
    {
        $normas = Norma::factory(4)
            ->sequence(fn ($sequence) => ['title' => $sequence->index . ' ' . $this->faker->unique()->sentence(3, true)])
            ->create();
        $user = User::factory()->create();
        $user->normas()->attach($normas);

        $items = [];
        foreach ($normas as $norma) {
            $items[] = [
                'id' => $norma->id,
                'title' => $norma->title,
            ];
        }

        $routeName = 'api.v2.normas.index';
        $route = route($routeName, [
            'filters' => [
                'title,like,%' . $normas[0]->title . '%||title,like,%' . $normas[2]->title . '%,or',
            ],
            'fields' => ['id', 'title'],
            'count' => ['legalDomains'],
            'sort' => 'title',
        ]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $response->assertJson(['data' => [$items[0], $items[2]]]);
        // as we only asked for id and title fields, address should not be included
        $response->assertJsonMissing(['data' => ['address' => $normas[0]->address]]);
        $response->assertJsonMissing(['data' => [$items[1]]]);

        // test with only one filter
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $normas[1]->title,
            ],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test multiple with subquery
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $normas[1]->title . '||geo_lat,eq,' . $normas[2]->geo_lat . ',or||or',
            ],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1], $items[2]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test multiple with subquery
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $normas[1]->title . '||geo_lat,eq,' . $normas[2]->geo_lat . ',or',
                'title,eq,' . $normas[1]->title . '||geo_lat,eq,' . $normas[2]->geo_lat . ',or||or',
            ],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1], $items[2]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test sorting title in reverse order
        $route = route($routeName, [
            'sort' => '-title,created_at',
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[3]]]);
        $data = json_decode($response->getContent(), true)['data'];
        $this->assertTrue($data[0]['id'] === $normas[3]->id);

        // filtering by whereHas relation
        $location = $normas[1]->location;
        $route = route($routeName, [
            'has' => ['location|id,In,[' . $location->id . ']'],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);
        // multiple whereHas with OR - should still return all though, because we're just testing the between
        $route = route($routeName, [
            'has' => ['location|id,In,[' . $location->id . ']||location|created_at,between,[' . now()->subDay()->format('Y-m-d') . ', ' . now()->addDay()->format('Y-m-d') . ']'],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => $items]);

        // filtering by has relation
        $location = $normas[1]->location;
        $domain = LegalDomain::factory()->create();
        $team = Team::factory()->create();
        $normas[1]->legalDomains()->attach($domain->id);
        $normas[2]->teams()->attach($team->id);
        $route = route($routeName, [
            'has' => ['legalDomains'],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);
        // multi has with OR
        $route = route($routeName, [
            'has' => ['legalDomains||teams'],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1], $items[2]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test ids
        $route = route($routeName, [
            'ids' => $normas[1]->id . ',' . $normas[2]->id,
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1], $items[2]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test is null
        $route = route($routeName, [
            'filters' => ['title,=,null'],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJsonMissing(['data' => [$items[0]]]);
    }

    public function testFilterExceptions(): void
    {
        $normas = Norma::factory(2)
            ->sequence(fn ($sequence) => ['title' => $sequence->index . ' ' . $this->faker->unique()->sentence(3, true)])
            ->create();
        $user = User::factory()->create();
        $user->normas()->attach($normas);
        $routeName = 'api.v2.normas.index';

        // should just ignore, also tests count passed as string
        $route = route($routeName, [
            'has' => ['location|id,In'],
            'count' => 'legalDomains',
        ]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $response->assertSuccessful();

        $route = route($routeName, [
            'filters' => [
                'title,' . $normas[1]->title,
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Please make sure the filter value is in the correct format.']);

        // test with an illegal operand
        $route = route($routeName, [
            'filters' => [
                'title,==,' . $normas[1]->title,
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Please make sure the you use the correct operand for the given filters']);

        // = can only be used for null
        $route = route($routeName, [
            'filters' => [
                'title,=,' . $normas[1]->title,
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Please make sure the you use the correct operand for the given filters']);

        // = can only be used for null
        $route = route($routeName, [
            'filters' => [
                'title,=,null',
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(200);
    }

    public function testShowRelations(): void
    {
        $norma = Norma::factory()->create();
        $country = Location::factory()->create();
        $legalDomain = LegalDomain::factory()->create();
        $norma->location->update(['location_country_id' => $country->id]);
        $norma->legalDomains()->attach($legalDomain);
        $user = User::factory()->create();
        $user->normas()->attach($norma);

        $routeName = 'api.v2.normas.show';
        // adding users at the end to make sure it's not being included
        $route = route($routeName, ['id' => $norma->id, 'include' => 'legalDomains|location|location.locationType|location.country|users']);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $item = [
            'id' => $norma->id,
            'title' => $norma->title,
            'location' => [
                'id' => $norma->location->id,
                'title' => $norma->location->title,
                'parent_id' => $norma->location->parent_id,
                'location_country_id' => $norma->location->location_country_id,
                'flag' => $norma->location->flag,
                'country' => [
                    'id' => $country->id,
                    'title' => $country->title,
                ],
                'locationType' => [
                    'id' => $norma->location->locationType->id,
                    'title' => $norma->location->locationType->title,
                ],
            ],
            'legalDomains' => [
                [
                    'id' => $legalDomain->id,
                    'title' => $legalDomain->title,
                ],
            ],
        ];

        $response->assertJson([
            'data' => $item,
        ], true);

        // make sure users isn't added even though requested in the include
        $response->assertJsonMissing(['data' => ['users' => []]]);
    }
}
