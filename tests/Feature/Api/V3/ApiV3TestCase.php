<?php

namespace Tests\Feature\Api\V3;

use App\Models\Customer\Organisation;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class ApiV3TestCase extends TestCase
{
    use CompilesStream;

    protected function assertUnauthorizedThenRun(
        Organisation $organisation,
        string $method,
        string $route,
        array $data = [],
        array $headers = []
    ): TestResponse {
        $response = $this->json($method, $route, $data, $headers);

        $response->assertUnauthorized();

        Passport::actingAsClient(
            Client::factory()->create(['organisation_id' => $organisation->id])
        );

        return $this->json($method, $route, $data, $headers);
    }
}
