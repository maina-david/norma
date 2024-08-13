<?php

namespace Tests\Unit\Services\Search\Elastic;

use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Models\Corpus\ReferenceContent;
use App\Services\Search\Elastic\Client;
use App\Services\Search\Elastic\DocumentSearchIndexer;
use Exception;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class DocumentSearchIndexerTest extends TestCase
{
    use CompilesStream;

    private function mockEsClient(): void
    {
        $mock = $this->mock(Client::class, function (MockInterface $mock) {
            $mock->allows([
                'bulk' => [],
                'delete' => [],
                'index' => [],
            ]);
        });
    }

    public function testAddReferences(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $this->mockEsClient();
        $work->references->load(['htmlContent', 'refPlainText']);
        $indexer = app(DocumentSearchIndexer::class);

        $this->assertNull($indexer->addReferences($work->references));
    }

    public function testAddReferencesException(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('bulk')->andThrow(new Exception());
        });
        $indexer = app(DocumentSearchIndexer::class);
        $work->references->load(['htmlContent', 'refPlainText']);
        $this->assertNull($indexer->addReferences($work->references));
    }

    public function testAddByWork(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $work->references[0]->htmlContent()->save(new ReferenceContent(['reference_id' => $work->references[0]->id, 'cached_content' => 'Test content']));

        $this->mockEsClient();
        $indexer = app(DocumentSearchIndexer::class);
        $this->assertNull($indexer->addByWork($work->id));
    }

    public function testAddDocToIndex(): void
    {
        $this->mockEsClient();
        $indexer = app(DocumentSearchIndexer::class);
        $this->assertNull($indexer->addDocToIndex('references', 1, []));
    }

    public function testDeleteDoc(): void
    {
        $this->mockEsClient();
        $indexer = app(DocumentSearchIndexer::class);
        $this->assertNull($indexer->deleteDoc('references', 1));
    }

    public function testDeleteReference(): void
    {
        $this->mockEsClient();
        $indexer = app(DocumentSearchIndexer::class);
        $this->assertNull($indexer->deleteReference(1));
    }

    public function testFullIndex(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $ref = $work->references[0];
        $ref->htmlContent()->save(new ReferenceContent([
            'reference_id' => $ref->id,
            'cached_content' => 'Test content',
        ]));

        $this->mockEsClient();
        $indexer = app(DocumentSearchIndexer::class);
        $indexer->fullIndex();

        Bus::fake();

        $indexer->fullIndex();
        Bus::assertDispatched(SearchIndexWork::class);
    }
}
