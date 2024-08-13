<?php

namespace Tests\Feature\Api\V2\Corpus;

use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Models\Corpus\Work;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class IndexWorkControllerTest extends TestCase
{
    public function testIndexWork(): void
    {
        Bus::fake();

        $work = Work::factory()->create();
        $route = route('api.v2.public.corpus.webhook.index-work', ['work' => $work]);
        $this->json('post', $route)
            ->assertUnauthorized();

        $this->json('post', $route, [], ['Authorization' => 'secret'])
            ->assertSuccessful();

        Bus::assertDispatched(SearchIndexWork::class);
    }
}
