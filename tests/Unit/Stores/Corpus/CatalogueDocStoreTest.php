<?php

namespace Tests\Unit\Stores\Corpus;

use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use App\Models\Arachno\SourceCategory;
use App\Models\Corpus\CatalogueDoc;
use App\Stores\Corpus\CatalogueDocStore;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Tests\TestCase;

class CatalogueDocStoreTest extends TestCase
{
    public function testCreateFail(): void
    {
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create(['source_unique_id' => null]);
        /** @var Crawler */
        $crawler = Crawler::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/source_unique_id is required/');
        app(CatalogueDocStore::class)->create($doc, $crawler);
    }

    public function testCreateFail2(): void
    {
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create(['source_unique_id' => '12345']);
        /** @var Crawler */
        $crawler = Crawler::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/start_url is required for/');
        app(CatalogueDocStore::class)->create($doc, $crawler);
    }

    public function testCreate(): void
    {
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create(['title' => 'Test Title', 'source_unique_id' => '12345', 'start_url' => 'http://example.com', 'language_code' => 'eng', 'source_id' => Source::factory()->create()->id]);
        $doc->setUid()->save();

        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        $newDoc = app(CatalogueDocStore::class)->create($doc, $crawler);

        $newDoc->refresh();
        $this->assertSame($crawler->source_id, $newDoc->source_id);
        $this->assertSame($crawler->id, $newDoc->crawler_id);
    }

    public function testAttachSourceCategories(): void
    {
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create();
        /** @var Collection<SourceCategory> */
        $sourceCats = SourceCategory::factory(3)->create();
        app(CatalogueDocStore::class)->attachSourceCategories($doc, $sourceCats);

        $this->assertTrue(
            $doc->sourceCategories->contains(
                $sourceCats->first()
            )
        );
    }
}
