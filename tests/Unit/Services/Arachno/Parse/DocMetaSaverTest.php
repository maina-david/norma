<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Arachno\UrlFrontierMeta;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Keyword;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\WorkType;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaSaver;
use App\Services\Arachno\Parse\PdfExtractor;
use App\Services\Language\LanguageDetector;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use RuntimeException;

class DocMetaSaverTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function testSetDocMetaPropertyFail(): void
    {
        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['crawl_id' => $this->crawl?->id]);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/source_unique_id needs to be set/');
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'title', 'bla');
    }

    public function testSetDocMetaProperty(): void
    {
        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $this->crawl?->id]);
        $downloadUrl = 'http://some-url.com';
        /** @var UrlFrontierMeta */
        $frontierMeta = UrlFrontierMeta::factory()->create(['url_frontier_link_id' => $pageUrl->id, 'meta' => ['language_code' => 'deu', 'download_url' => $downloadUrl]]);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'source_unique_id', '12345');

        /** @var Doc */
        $doc = $pageCrawl->getDoc();
        $this->assertNotNull($doc);
        $this->assertSame('12345', $doc->source_unique_id);

        /** @var Location */
        $location = Location::factory()->create();
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'primary_location', (string) $location->id);
        $this->assertSame($location->id, $pageCrawl->getDoc()?->primary_location_id);
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'primary_location', $location->slug);
        $this->assertSame($location->id, $pageCrawl->getDoc()?->primary_location_id);

        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->assertSame('deu', $pageCrawl->getDoc()?->docMeta->language_code);

        $title = 'Zweite Verordnung über Basismaßnahmen zum Schutz der Bevölkerung vor Infektionen mit dem Coronavirus';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'title', $title);
        $this->assertSame($title, $pageCrawl->getDoc()?->title);
        $this->assertNotNull($pageCrawl->getDoc()?->docMeta->title_translation);

        /** @var WorkType */
        $workType = WorkType::factory()->create();
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'work_type', $workType->slug);
        $this->assertSame($workType->id, $pageCrawl->getDoc()?->docMeta->work_type_id);

        $prop = 'http://example.com';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'source_url', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->source_url);

        $prop = 'http://example.com';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'download_url', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->download_url);

        $prop = '123445';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'work_number', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->work_number);

        $prop = '54321';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'publication_number', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->publication_number);

        $prop = '6574747';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'publication_document_number', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->publication_document_number);

        $prop = '2022-01-05';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'work_date', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->work_date);

        $prop = '2018-01-20';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'effective_date', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->effective_date);

        /** @var ContentResource */
        $resource = ContentResource::factory()->create();
        $this->partialMock(ContentResourceStore::class, function (MockInterface $mock) use ($resource) {
            $mock->shouldReceive('storeResource')->andReturn($resource);
        });
        $prop = 'A summary of the text';
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'summary', $prop);
        $this->assertSame($prop, $pageCrawl->getDoc()?->docMeta->summary);
        $this->assertSame($resource->id, $pageCrawl->getDoc()?->docMeta->summary_content_resource_id);

        $prop = ['hello', 'world'];
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'keywords', $prop);
        /** @var Keyword */
        $world = Keyword::where('label', 'world')->first();
        $this->assertNotNull($world);
        $this->assertTrue($pageCrawl->getDoc()?->keywords->contains($world));

        /** @var Collection<LegalDomain> */
        $domains = LegalDomain::factory(3)->create();
        app(DocMetaSaver::class)->setDocMetaProperty($pageCrawl, 'legal_domains', $domains->modelKeys());
        $this->assertTrue($pageCrawl->getDoc()?->legalDomains->contains($domains[0]));
    }

    public function testDetectLanguageCode(): void
    {
        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $this->crawl?->id]);

        /** @var Crawl */
        $crawl = $this->crawl;
        $text = 'Le rouge est une couleur vive. C’est la couleur de nombreux fruits et légumes, comme les tomates, les fraises ou les cerises.';
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->for($crawl)->create(['response_body' => '', 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, [], $contentCache);

        $this->partialMock(PdfExtractor::class, function (MockInterface $mock) use ($text) {
            $mock->shouldReceive('getText')->andReturn($text);
        });

        $this->partialMock(LanguageDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->andReturn('fra');
        });

        $detected = app(DocMetaSaver::class)->detectLanguageCode($pageCrawl);
        $this->assertSame('fra', $detected);

        /** @var ContentCache */
        $contentCache = ContentCache::factory()->for($crawl)->create(['response_body' => $this->domCrawler->outerHtml(), 'response_headers' => ['Content-Type' => ['text/html']]]);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, [], $contentCache);

        $this->partialMock(LanguageDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->andReturn('deu');
        });
        $detected = app(DocMetaSaver::class)->detectLanguageCode($pageCrawl);
        $this->assertSame('deu', $detected);
    }

    public function testGetWorkTypeByText(): void
    {
        /** @var WorkType */
        $workTypeAct = WorkType::factory()->create(['slug' => 'act']);
        /** @var WorkType */
        $workType = WorkType::factory()->create();
        $found = app(DocMetaSaver::class)->getWorkTypeByText((string) $workType->id);
        $this->assertSame($workType->id, $found?->id);

        $notFound = app(DocMetaSaver::class)->getWorkTypeByText('does not exist as work type');
        // should default to Act work type
        $this->assertSame($workTypeAct->id, $notFound?->id);
    }
}
