<?php

namespace Tests\Feature\Corpus\My;

use App\Enums\System\LibryoModule;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Search\Elastic\Client;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class ReferenceControllerTest extends MyTestCase
{
    private function mockEsClient(Reference $reference, float $score): void
    {
        $mock = $this->mock(Client::class, function (MockInterface $mock) use ($reference, $score) {
            $mock->allows([
                'search' => [
                    'hits' => ['hits' => [['_score' => $score, '_source' => $reference->toArray(), '_id' => $reference->id]], 'total' => ['value' => 1]],
                    'aggregations' => ['unique_works' => ['buckets' => [['key' => 1], ['key' => 2]]]],
                    '_scroll_id' => '123',
                ],
                'scroll' => ['_scroll_id' => '123', 'hits' => ['hits' => [], 'total' => ['value' => 1]]],
                'get' => [],
            ]);
        });
    }

    private function mockEsClientEmptyResponse(): void
    {
        $mock = $this->mock(Client::class, function (MockInterface $mock) {
            $mock->allows([
                'search' => [
                    'hits' => ['hits' => [], 'total' => ['value' => 0]],
                ],
            ]);
        });
    }

    public function testIndexDetailedRequirements(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        [,, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($libryo);
        $reference = $work->references->load(['citation', 'refSelector', 'refPlainText'])->first();
        $this->mockEsClient($reference, 32.5);

        $routeName = 'my.corpus.references.index';
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);

        app(ActiveLibryosManager::class)->activateAll($user);

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($work->title);

        $response = $this->get(route($routeName, ['search' => $reference->refPlainText?->plain_text]))->assertSuccessful();
        $response->assertSee($work->title);
        $response->assertSee($reference->refPlainText?->plain_text);

        $response = $this->get(route($routeName, ['tags' => [$tag->id], 'domains' => [$domain->id]]))->assertSuccessful();
        $response->assertSee($work->title);
        // check filters also work with legacy tags
        $tag->update(['old_tag_id' => uniqid()]);
        $response = $this->get(route($routeName, ['tags' => [$tag->old_tag_id]]))->assertSuccessful();
        $response->assertSee($work->title);

        $response = $this->get(route($routeName, ['works' => [$work->id]]))->assertSuccessful();
        $response->assertSee($work->title);

        $response = $this->get(route($routeName, ['jurisdictionTypes' => [$reference->load('locations')->locations->first()->location_type_id]]))
            ->assertSuccessful();
        $response->assertSee($work->title);
    }

    public function testIndexDetailedRequirementsEmptySearch(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        [,, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($libryo);
        $reference = $work->references->load(['citation', 'refPlainText'])->first();
        $this->mockEsClientEmptyResponse();

        $routeName = 'my.corpus.references.index';
        $response = $this->get(route($routeName, ['search' => Str::random(40)]))->assertSuccessful();
        $response->assertDontSee($work->title);
        $response->assertDontSee($reference->refPlainText?->plain_text);
    }

    public function testShow(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        [,, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($libryo);
        $reference = $work->references->load(['citation', 'refPlainText'])->first();

        /** @var \App\Models\Customer\Libryo $libryo */
        $libryo->enableModule(LibryoModule::actions());

        $routeName = 'my.corpus.references.show';
        $response = $this->get(route($routeName, ['reference' => $reference]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
    }
}
