<?php

namespace Tests\Feature\Api\V2\Arachno;

use App\Models\Arachno\ChangeAlert;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Api\ApiTestCase;

class WacheteWebhookControllerTest extends ApiTestCase
{
    public function testHandleGeneric(): void
    {
        $routeName = 'api.v2.public.arachno.webhook.wachete.generic';
        $count = ChangeAlert::count();
        $payload = [
            'taskId' => '2a4c09ca-c166-42f5-a7eb-dda045a1e674',
            'alertId' => 'WDFE6GA3Y...',
            'userId' => '54c84df2-3f01-4419-80d1-1d546c4ee81e',
            'name' => 'CNN highlights',
            'url' => 'http://cnn.com/',
            'alertUrl' => 'https://www.wachete.com/wachet/2a4c09ca-c166-42f5-a7eb-dda01e674?alertId=WDFE6GA3Y...',
            'current' => 'President changed his plans for visit',
            'previous' => 'Big storm is coming this night',
            'nested' => [],
        ];
        $headers = [
            'x-wachete-signature' => 'd3dad80f52193d44361d004ef5bad12ad01de88abacb843f219c5c716ff3248b',
        ];
        // should fail first because signature does not match
        $this->withExceptionHandling()->postJson(route($routeName), $payload, $headers)
            ->assertForbidden();

        // the above signature was signed with this signature, so should pass now
        Config::set('services.wachete.webhook_secret', '1234');
        $this->withExceptionHandling()->postJson(route($routeName), $payload, $headers)
            ->assertSuccessful();

        $this->assertGreaterThan($count, ChangeAlert::count());
    }
}
