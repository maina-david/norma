<?php

namespace Tests\Feature\Api\V2\Notify;

use App\Actions\Notify\LegalUpdate\SyncFromWork;
use App\Models\Notify\LegalUpdate;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncLegalUpdateControllerTest extends TestCase
{
    public function testSyncUpdate(): void
    {
        Queue::fake();

        $update = LegalUpdate::factory()->create();
        $route = route('api.v2.public.corpus.webhook.sync-legal-update', ['update' => $update]);
        $this->json('post', $route)
            ->assertUnauthorized();

        $this->json('post', $route, [], ['Authorization' => 'wrongsecret'])
            ->assertUnauthorized();

        $this->json('post', $route, [], ['Authorization' => 'secret'])
            ->assertSuccessful();

        SyncFromWork::assertPushed();
    }
}
