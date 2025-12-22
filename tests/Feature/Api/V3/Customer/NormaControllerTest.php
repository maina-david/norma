<?php

namespace Tests\Feature\Api\V3\Customer;

use App\Models\Customer\Norma;
use Tests\Feature\Api\V3\ApiV3TestCase;

class NormaControllerTest extends ApiV3TestCase
{
    public function testNormasForOrganisation(): void
    {
        /** @var Norma $norma */
        [$norma, $organisation] = $this->initCompiledStream();

        $otherNorma = Norma::factory()->create(['organisation_id' => $organisation->id]);

        $responseNorma = [
            'id' => $norma->id,
            'title' => $norma->title,
            'geo_lat' => $norma->geo_lat,
            'geo_lng' => $norma->geo_lng,
            'address' => $norma->address,
            'description' => $norma->description,
            'link' => route('my.normas.activate.redirect', [
                'norma' => $norma->id,
                'redirect' => route('my.dashboard', [], false),
            ], false),
        ];
        $responseOtherNorma = [
            'id' => $otherNorma->id,
            'title' => $otherNorma->title,
            'geo_lat' => $otherNorma->geo_lat,
            'geo_lng' => $otherNorma->geo_lng,
            'address' => $otherNorma->address,
            'description' => $otherNorma->description,
            'link' => route('my.normas.activate.redirect', [
                'norma' => $otherNorma->id,
                'redirect' => route('my.dashboard', [], false),
            ], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.streams.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
                    $responseOtherNorma,
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

        $streams = $norma->id;

        $route = route('api.v3.streams.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
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

        $route = route('api.v3.streams.show', ['organisation' => $organisation->id, 'stream' => $otherNorma->id]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherNorma,
            ]);
    }
}
