<?php

namespace Tests\Feature\Api\V1\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Tests\Feature\Api\ApiTestCase;

class LibryoControllerTest extends ApiTestCase
{
    public function testIndex(): void
    {
        $libryos = Libryo::factory(2)->create();

        $user = User::factory()->create();

        $user->libryos()->attach($libryos);

        $routeName = 'api.v1.libryos.index';
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
                'library_id' => $libryo->library_id,
                'organisation_id' => $libryo->organisation_id,
                'deactivated' => $libryo->deactivated,
                'description' => $libryo->description,
                'compilation_in_progress' => $libryo->compilation_in_progress,
                'created_at' => $libryo->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $libryo->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // make sure legacy v1 response stays the same
        $pagination = [
            'total' => $libryos->count(),
            'page' => 'all',
            'perPage' => 'all',
        ];

        $response->assertJson([
            'data' => $items,
            'meta' => [
                'pagination' => $pagination,
            ],
        ], true);

        // test with pagination
        $pagination = [
            'total' => $libryos->count(),
            'page' => 1,
            'perPage' => 1,
        ];
        $route = route($routeName, ['perPage' => 1, 'page' => 1]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[0]], 'meta' => ['pagination' => $pagination]]);
        $response->assertJsonMissing(['data' => [$items[1]]]);
    }

    public function testShow(): void
    {
        $libryo = Libryo::factory()->create();
        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $routeName = 'api.v1.libryos.show';
        // adding users at the end to make sure it's not being included
        $route = route($routeName, ['id' => $libryo->id]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $item = [
            'id' => $libryo->id,
            'title' => $libryo->title,
        ];

        $response->assertJson([
            'data' => $item,
        ], true);
    }
}
