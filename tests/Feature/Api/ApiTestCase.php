<?php

namespace Tests\Feature\Api;

use App\Models\Auth\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class ApiTestCase extends TestCase
{
    use CompilesStream;

    protected function assertApiUnauthorizedThenRun(?User $user, string $method, string $route, array $data = [], array $headers = [])
    {
        $response = $this->json($method, $route, $data, $headers);

        $response->assertUnauthorized();

        Passport::actingAs(
            $user ?? User::factory()->create()
        );

        $response = $this->json($method, $route, $data, $headers);

        return $response;
    }
}
