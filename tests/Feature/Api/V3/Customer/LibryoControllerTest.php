<?php

namespace Tests\Feature\Api\V3\Customer;

use App\Models\Customer\Libryo;
use Tests\Feature\Api\V3\ApiV3TestCase;

class LibryoControllerTest extends ApiV3TestCase
{
    public function testLibryosForOrganisation(): void
    {
        /** @var Libryo $libryo */
        [$libryo, $organisation] = $this->initCompiledStream();

        $otherLibryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);

        $responseLibryo = [
            'id' => $libryo->id,
            'title' => $libryo->title,
            'geo_lat' => $libryo->geo_lat,
            'geo_lng' => $libryo->geo_lng,
            'address' => $libryo->address,
            'description' => $libryo->description,
            'link' => route('my.libryos.activate.redirect', [
                'libryo' => $libryo->id,
                'redirect' => route('my.dashboard', [], false),
            ], false),
        ];
        $responseOtherLibryo = [
            'id' => $otherLibryo->id,
            'title' => $otherLibryo->title,
            'geo_lat' => $otherLibryo->geo_lat,
            'geo_lng' => $otherLibryo->geo_lng,
            'address' => $otherLibryo->address,
            'description' => $otherLibryo->description,
            'link' => route('my.libryos.activate.redirect', [
                'libryo' => $otherLibryo->id,
                'redirect' => route('my.dashboard', [], false),
            ], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.streams.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
                    $responseOtherLibryo,
                ],
                'links' => [
                    'first' => route('api.v3.streams.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'last' => route('api.v3.streams.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.streams.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = $libryo->id;

        $route = route('api.v3.streams.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
                ],
                'links' => [
                    'first' => route('api.v3.streams.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.streams.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.streams.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 1,
                    'total' => 1,
                ],
            ]);

        $route = route('api.v3.streams.show', ['organisation' => $organisation->id, 'stream' => $otherLibryo->id]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherLibryo,
            ]);
    }
}
