<?php

namespace Tests\Feature\Api\V2\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Api\ApiTestCase;

class LibryoControllerTest extends ApiTestCase
{
    public function testIndex(): void
    {
        $libryos = Libryo::factory(2)->create();
        $libryoUnattached = Libryo::factory()->create();
        $user = User::factory()->create();
        $user->libryos()->attach($libryos);

        $routeName = 'api.v2.libryos.index';
        $route = route($routeName);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        foreach ($libryos as $libryo) {
            $items[] = [
                'id' => $libryo->id,
                'title' => $libryo->title,
                'place_type_id' => $libryo->place_type_id,
                'address' => $libryo->address,
                'geo_lat' => $libryo->geo_lat,
                'geo_lng' => $libryo->geo_lng,
                'location_id' => $libryo->location_id,
                'organisation_id' => $libryo->organisation_id,
                'deactivated' => $libryo->deactivated,
                'description' => $libryo->description,
                'compilation_in_progress' => $libryo->compilation_in_progress,
                'created_at' => $libryo->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $libryo->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        $response->assertJson([
            'data' => $items,
        ], true);
        $response->assertJsonMissing(['data' => ['id' => $libryoUnattached->id]]);

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
     * We'll use the LibryoController to test all the API filters.
     *
     * @return void
     */
    public function testIndexFilters(): void
    {
        $libryos = Libryo::factory(4)
            ->sequence(fn ($sequence) => ['title' => $sequence->index . ' ' . $this->faker->unique()->sentence(3, true)])
            ->create();
        $user = User::factory()->create();
        $user->libryos()->attach($libryos);

        $items = [];
        foreach ($libryos as $libryo) {
            $items[] = [
                'id' => $libryo->id,
                'title' => $libryo->title,
            ];
        }

        $routeName = 'api.v2.libryos.index';
        $route = route($routeName, [
            'filters' => [
                'title,like,%' . $libryos[0]->title . '%||title,like,%' . $libryos[2]->title . '%,or',
            ],
            'fields' => ['id', 'title'],
            'count' => ['legalDomains'],
            'sort' => 'title',
        ]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $response->assertJson(['data' => [$items[0], $items[2]]]);
        // as we only asked for id and title fields, address should not be included
        $response->assertJsonMissing(['data' => ['address' => $libryos[0]->address]]);
        $response->assertJsonMissing(['data' => [$items[1]]]);

        // test with only one filter
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $libryos[1]->title,
            ],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test multiple with subquery
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $libryos[1]->title . '||geo_lat,eq,' . $libryos[2]->geo_lat . ',or||or',
            ],
        ]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[1], $items[2]]]);
        $response->assertJsonMissing(['data' => [$items[0]]]);

        // test multiple with subquery
        $route = route($routeName, [
            'filters' => [
                'title,eq,' . $libryos[1]->title . '||geo_lat,eq,' . $libryos[2]->geo_lat . ',or',
                'title,eq,' . $libryos[1]->title . '||geo_lat,eq,' . $libryos[2]->geo_lat . ',or||or',
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
        $this->assertTrue($data[0]['id'] === $libryos[3]->id);

        // filtering by whereHas relation
        $location = $libryos[1]->location;
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
        $location = $libryos[1]->location;
        $domain = LegalDomain::factory()->create();
        $team = Team::factory()->create();
        $libryos[1]->legalDomains()->attach($domain->id);
        $libryos[2]->teams()->attach($team->id);
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
            'ids' => $libryos[1]->id . ',' . $libryos[2]->id,
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
        $libryos = Libryo::factory(2)
            ->sequence(fn ($sequence) => ['title' => $sequence->index . ' ' . $this->faker->unique()->sentence(3, true)])
            ->create();
        $user = User::factory()->create();
        $user->libryos()->attach($libryos);
        $routeName = 'api.v2.libryos.index';

        // should just ignore, also tests count passed as string
        $route = route($routeName, [
            'has' => ['location|id,In'],
            'count' => 'legalDomains',
        ]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $response->assertSuccessful();

        $route = route($routeName, [
            'filters' => [
                'title,' . $libryos[1]->title,
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Please make sure the filter value is in the correct format.']);

        // test with an illegal operand
        $route = route($routeName, [
            'filters' => [
                'title,==,' . $libryos[1]->title,
            ],
        ]);
        $response = $this->withExceptionHandling()->json('get', $route);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Please make sure the you use the correct operand for the given filters']);

        // = can only be used for null
        $route = route($routeName, [
            'filters' => [
                'title,=,' . $libryos[1]->title,
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
        $libryo = Libryo::factory()->create();
        $country = Location::factory()->create();
        $legalDomain = LegalDomain::factory()->create();
        $libryo->location->update(['location_country_id' => $country->id]);
        $libryo->legalDomains()->attach($legalDomain);
        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.libryos.show';
        // adding users at the end to make sure it's not being included
        $route = route($routeName, ['id' => $libryo->id, 'include' => 'legalDomains|location|location.locationType|location.country|users']);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $item = [
            'id' => $libryo->id,
            'title' => $libryo->title,
            'location' => [
                'id' => $libryo->location->id,
                'title' => $libryo->location->title,
                'parent_id' => $libryo->location->parent_id,
                'location_country_id' => $libryo->location->location_country_id,
                'flag' => $libryo->location->flag,
                'country' => [
                    'id' => $country->id,
                    'title' => $country->title,
                ],
                'locationType' => [
                    'id' => $libryo->location->locationType->id,
                    'title' => $libryo->location->locationType->title,
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
