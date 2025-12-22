<?php

namespace Tests\Feature\Api\V1\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Tests\Feature\Api\ApiTestCase;

class NormaControllerTest extends ApiTestCase
{
    public function testIndex(): void
    {
        $normas = Norma::factory(2)->create();

        $user = User::factory()->create();

        $user->normas()->attach($normas);

        $routeName = 'api.v1.normas.index';
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
                'library_id' => $norma->library_id,
                'organisation_id' => $norma->organisation_id,
                'deactivated' => $norma->deactivated,
                'description' => $norma->description,
                'compilation_in_progress' => $norma->compilation_in_progress,
                'created_at' => $norma->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $norma->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // make sure legacy v1 response stays the same
        $pagination = [
            'total' => $normas->count(),
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
            'total' => $normas->count(),
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
        $norma = Norma::factory()->create();
        $user = User::factory()->create();
        $user->normas()->attach($norma);

        $routeName = 'api.v1.normas.show';
        // adding users at the end to make sure it's not being included
        $route = route($routeName, ['id' => $norma->id]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $item = [
            'id' => $norma->id,
            'title' => $norma->title,
        ];

        $response->assertJson([
            'data' => $item,
        ], true);
    }
}
