<?php

namespace Tests\Unit\Services\Search\Elastic;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Services\Search\Elastic\Client;
use App\Services\Search\Elastic\DocumentSearch;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class DocumentSearchTest extends TestCase
{
    use CompilesStream;

    private function mockEsClient(?Reference $reference = null): void
    {
        $mock = $this->mock(Client::class, function (MockInterface $mock) use ($reference) {
            $mock->allows([
                'search' => [
                    'hits' => ['hits' => [['_id' => $reference->id ?? null, '_source' => ['work_id' => 0], 'highlight' => ['content_eng' => ['Some highlighted text']]]], 'total' => ['value' => 1]],
                    'aggregations' => ['unique_works' => ['buckets' => [['key' => 1], ['key' => 2]]]],
                    '_scroll_id' => '123',
                ],
                'scroll' => ['_scroll_id' => '123', 'hits' => ['hits' => [], 'total' => ['value' => 1]]],
                'get' => [],
            ]);
        });
    }

    public function testSearchReferences(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $ref = $work->references[0];
        $ref->htmlContent()->save(new ReferenceContent([
            'reference_id' => $ref->id,
            'cached_content' => 'Test content',
        ]));
        $this->mockEsClient();
        $service = app(DocumentSearch::class);
        $service->searchReferences('test', $work->references->modelKeys());
        $service->searchReferences('test', $work->references->modelKeys(), 0, 20, ['title']);

        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('search')->andThrow(new BadRequest400Exception());
        });
        $this->assertArrayHasKey('hits', $service->searchReferences('test', $work->references->modelKeys()));
    }

    public function testGetReferenceHighlights(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $ref = $work->references[0];
        $ref->htmlContent()->save(new ReferenceContent([
            'reference_id' => $ref->id,
            'cached_content' => 'Test content',
        ]));
        $this->mockEsClient();
        $service = app(DocumentSearch::class);
        $this->assertArrayHasKey('hits', $service->getReferenceHighlights('test', $work->references->modelKeys()));
        $this->assertArrayHasKey('hits', $service->getReferenceHighlights('test', $work->references->modelKeys(), 0, 20, ['title']));
    }

    public function testGetReferencesExceptions(): void
    {
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('search')->andThrow(new BadRequest400Exception())->twice();
        });
        $service = app(DocumentSearch::class);
        $service->getReferenceHighlights('test', []);
        $service->searchReferences('test', []);
    }

    public function testGetReference(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearch::class);
        $this->assertEmpty($service->getReference(1));
    }

    public function testGetIndexedWorks(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearch::class);
        $this->assertCount(2, $service->getIndexedWorks());
    }

    public function testGetUnindexedWorks(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $ref = $work->references[0];
        $ref->htmlContent()->save(new ReferenceContent([
            'reference_id' => $ref->id,
            'cached_content' => 'Test content',
        ]));
        $this->mockEsClient();
        $service = app(DocumentSearch::class);
        $this->assertCount(1, $service->getUnindexedWorks());
    }

    public function testAddHighlightsToResults(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $reference = $work->references->first();
        $this->mockEsClient($reference);
        $works = new LengthAwarePaginator((new Work())->newCollection([$work]), 1, 50);
        $resultWorks = app(DocumentSearch::class)->addHighlightsToResults($works, 'test');

        $firstRef = $resultWorks->items()[0]->references->first();
        $this->assertStringContainsString('Some highlighted text', $firstRef->_searchHighlights[0]);
    }
}
