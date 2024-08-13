<?php

namespace Tests\Feature\Traits;

use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;

trait TestsVisibleFormLabels
{
    /**
     * @return void
     */
    protected function assertSeeVisibleFormLabels(
        ArrayAccess|array $item,
        string $route,
        array $testLabels,
        array $testFields = [],
    ): TestResponse {
        $response = $this->get($route)->assertSuccessful();

        foreach ($testLabels as $label) {
            $response->assertSeeSelector('//label[text()[contains(.,"' . $label . '")]]');
        }

        foreach ($testFields as $field) {
            $value = Arr::get($item, $field);
            $response->assertSee($value);
        }

        return $response;
    }
}
