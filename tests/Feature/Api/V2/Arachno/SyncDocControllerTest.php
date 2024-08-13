<?php

namespace Tests\Feature\Api\V2\Arachno;

use App\Models\Corpus\WorkExpression;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncDocControllerTest extends TestCase
{
    public function testSyncDoc(): void
    {
        Bus::fake();

        $expression = WorkExpression::factory()->create();
        $route = route('api.v2.public.corpus.webhook.sync-docs', ['expression' => $expression]);
        $this->json('post', $route)
            ->assertUnauthorized();

        $this->json('post', $route, [], ['Authorization' => 'wrongsecret'])
            ->assertUnauthorized();

        $this->json('post', $route, [], ['Authorization' => 'secret'])
            ->assertSuccessful();
    }
}
