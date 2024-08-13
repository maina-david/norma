<?php

namespace Tests\Feature\Traits;

use Illuminate\Testing\TestResponse;

trait HasDestroyTests
{
    /**
     * @param string $resourceClass
     * @param string $route
     *
     * @return TestResponse
     */
    protected function destroyAndTestData(string $resourceClass, string $route): TestResponse
    {
        $count = $resourceClass::count();
        $response = $this->followingRedirects()->delete($route)->assertSuccessful();
        $this->assertLessThan($count, $resourceClass::count());

        return $response;
    }
}
