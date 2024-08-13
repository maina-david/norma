<?php

namespace Tests\Unit\Services\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Jobs\Arachno\OcrPdf;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\Source;
use App\Models\Arachno\SourceCategory;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Work;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DocMetaSaver;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;
use App\Services\Arachno\Parse\LinkExtractor;
use App\Services\Arachno\Parse\PageCapture;
use App\Services\Arachno\Parse\PdfCapture;
use App\Services\Arachno\Parse\TocCapture;
use App\Services\Arachno\Parse\TocItemDraft;
use App\Services\Arachno\Search\SearchPageSave;
use App\Stores\Corpus\CatalogueDocStore;
use DOMElement;
use Generator;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class ArachnoTest extends TestCase
{
    public function testMatches(): void
    {
        $this->partialMock(DomQueryRunner::class, function (MockInterface $mock) {
            $mock->shouldReceive('queryMatches')->andReturn(true);
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $query = new DomQuery(['type' => DomQueryType::CSS, 'query' => '.class']);
        $this->assertTrue(app(Arachno::class)->matchQuery($pageCrawl, $query));

        $this->assertTrue(app(Arachno::class)->matchUrl($pageCrawl, '/regex/'));
        $this->assertTrue(app(Arachno::class)->matchCSS($pageCrawl, '.class'));
        $this->assertTrue(app(Arachno::class)->matchXpath($pageCrawl, '//body'));
    }

    public function testFollow(): void
    {
        $this->partialMock(LinkExtractor::class, function (MockInterface $mock) {
            $mock->shouldReceive('addLinkToFrontier')->andReturn(new UrlFrontierLink());
            $mock->shouldReceive('extractLinksForQuery');
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);

        $this->assertInstanceOf(UrlFrontierLink::class, app(Arachno::class)->followLink($pageCrawl, new UrlFrontierLink()));

        $query = new DomQuery(['type' => DomQueryType::CSS, 'query' => '.class']);
        app(Arachno::class)->followLinks($pageCrawl, $query, true);
        $this->assertTrue(true);

        $query = '.class';
        app(Arachno::class)->followLinksCSS($pageCrawl, $query, true);
        $this->assertTrue(true);

        $query = '//body//a';
        app(Arachno::class)->followLinksXpath($pageCrawl, $query, true);
        $this->assertTrue(true);
    }

    public function testAddMetaToLink(): void
    {
        $this->partialMock(UrlFrontierLinkManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('addMetaToLink');
        });

        app(Arachno::class)->addMetaToLink(new UrlFrontierLink(), new DocMetaDto());

        $this->assertTrue(true);
    }

    public function testCapture(): void
    {
        $this->partialMock(PageCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('capture')->andReturn(new DomCrawler(''));
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $this->assertInstanceOf(DomCrawler::class, app(Arachno::class)->capture($pageCrawl));
    }

    public function testCaptureContent(): void
    {
        $this->partialMock(PageCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('store');
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        app(Arachno::class)->captureContent($pageCrawl, '<div>test</div>');
        $this->assertTrue(true);
    }

    public function testCapturePDF(): void
    {
        Bus::fake();
        $this->partialMock(PdfCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('capture')->once();
        });
        $contentCache = ContentCache::factory()->create(['response_body' => 'Test Pdf Body', 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $pageCrawl = Mockery::mock(PageCrawl::class)->makePartial();
        $pageCrawl->shouldReceive('setContentCache')->andReturnSelf();
        $pageCrawl->__construct(new DomCrawler(), new UrlFrontierLink(), [], $contentCache);
        $pageCrawl->shouldReceive('getFinalRedirectedUrl')->andReturn('http://test');
        $pageCrawl->shouldReceive('getDoc')->andReturn(Doc::factory()->create());

        app(Arachno::class)->capturePDF($pageCrawl);
        $this->assertTrue(true);

        $pageCrawl->setOcrSettings(['languages' => ['eng']]);
        app(Arachno::class)->capturePDF($pageCrawl);

        Bus::assertDispatched(OcrPdf::class);
    }

    public function testCreateCatalogueDoc(): void
    {
        $this->partialMock(CatalogueDocStore::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new CatalogueDoc());
        });

        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $this->assertInstanceOf(CatalogueDoc::class, app(Arachno::class)->createCatalogueDoc($pageCrawl, new CatalogueDoc(), new Crawler()));
    }

    public function testAddCategoriesToCatalogueDoc(): void
    {
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create();
        /** @var Collection<SourceCategory> */
        $sourceCats = SourceCategory::factory(3)->create();
        /** @var SourceCategory */
        $cat = $sourceCats->first();

        app(Arachno::class)->addCategoriesToCatalogueDoc($doc, $sourceCats->modelKeys());

        $this->assertTrue($doc->sourceCategories->contains($cat));
        $doc->sourceCategories()->detach();

        app(Arachno::class)->addCategoriesToCatalogueDoc($doc, $sourceCats->pluck('title')->toArray());
        $this->assertTrue($doc->sourceCategories->contains($cat));
    }

    public function testFetchChangedDocs(): void
    {
        $this->partialMock(UrlFrontierLinkManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('addToFrontier');
        });

        /** @var Source */
        $source = Source::factory()->create();
        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create([
            'source_id' => $source->id,
            'source_unique_id' => 'fetchChangedDocs',
            'crawler_id' => $crawler->id,
        ]);
        $doc->setUid()->save();
        /** @var Work */
        $work = Work::factory()->create(['uid' => $doc->uid]);

        app(Arachno::class)->fetchChangedDocs([$doc->source_unique_id], $source->id);

        $this->assertNotNull($doc->refresh()->fetch_started_at);
    }

    public function testFetchChangedUpdatedDocs(): void
    {
        $this->partialMock(UrlFrontierLinkManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('addToFrontier');
        });
        $time = now()->subDay();

        /** @var Source */
        $source = Source::factory()->create();
        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        /** @var CatalogueDoc */
        $doc = CatalogueDoc::factory()->create([
            'source_id' => $source->id,
            'source_unique_id' => 'fetchChangedDocs',
            'crawler_id' => $crawler->id,
            'last_updated_at' => $time->format('Y-m-d H:i:s'),
        ]);
        $doc->setUid()->save();
        /** @var Work */
        $work = Work::factory()->create(['uid' => $doc->uid]);

        $updatedCatDoc = new CatalogueDoc([
            'source_unique_id' => 'fetchChangedDocs',
            'last_updated_at' => $time->addDay()->format('Y-m-d H:i:s'),
        ]);
        app(Arachno::class)->fetchChangedUpdatedDocs(
            (new CatalogueDoc())->newCollection([$updatedCatDoc]),
            $source->id
        );

        $this->assertNotNull($doc->refresh()->fetch_started_at);
    }

    public function testSaveDoc(): void
    {
        /** @var Doc */
        $doc = Doc::factory()->create();
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $pageCrawl->setDoc($doc);
        $doc->title = 'New title';
        $doc->docMeta->title_translation = 'New title translation';

        $savedDoc = app(Arachno::class)->saveDoc($pageCrawl);
        $this->assertSame($doc->title, $savedDoc->title);
        $this->assertSame($doc->docMeta->title_translation, $savedDoc->docMeta->title_translation);
    }

    public function testSetDocMetaProperty(): void
    {
        $this->partialMock(DocMetaSaver::class, function (MockInterface $mock) {
            $mock->shouldReceive('setDocMetaProperty')->andReturn(new Doc());
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $this->assertInstanceOf(Doc::class, app(Arachno::class)->setDocMetaProperty($pageCrawl, 'title', 'new title'));

        $this->partialMock(DomQueryRunner::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTextByQuery')->andReturn('new title');
        });

        $this->assertInstanceOf(Doc::class, app(Arachno::class)->setDocMetaPropertyByCSS($pageCrawl, 'title', '.class'));
        $this->assertInstanceOf(Doc::class, app(Arachno::class)->setDocMetaPropertyByXpath($pageCrawl, 'title', '//body/a'));
    }

    public function testEach(): void
    {
        $domCrawler = new DomCrawler('<html><body></body></html>');
        $pageCrawl = new PageCrawl($domCrawler, new UrlFrontierLink(), []);
        $id = 'test';
        app(Arachno::class)->each($pageCrawl, 'body', fn (DOMElement $node) => $node->setAttribute('id', $id));
        $this->assertSame($id, $domCrawler->filter('body')->getNode(0)?->getAttribute('id'));
        $id = 'test2';
        app(Arachno::class)->eachX($pageCrawl, '//body', fn (DOMElement $node) => $node->setAttribute('id', $id));
        $this->assertSame($id, $domCrawler->filter('body')->getNode(0)?->getAttribute('id'));
    }

    public function testCaptureTocCSS(): void
    {
        $this->partialMock(TocCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('capture');
            $mock->shouldReceive('createItemsAndAddtoFrontier');
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        app(Arachno::class)->captureTocCSS($pageCrawl, '#toc');

        app(Arachno::class)->captureTocXpath($pageCrawl, '//toc');
        app(Arachno::class)->captureTocFromGenerator($pageCrawl, $this->_getGenerator());
        $this->assertTrue(true);
    }

    public function testTocItemArrayToGenerator(): void
    {
        $arr = [new TocItemDraft([])];
        $gen = app(Arachno::class)->tocItemArrayToGenerator($arr);
        $this->assertInstanceOf(Generator::class, $gen);
        foreach ($gen as $tocDraft) {
            $this->assertInstanceOf(TocItemDraft::class, $tocDraft);
        }
    }

    public function testCaptureTocFromDraftArray(): void
    {
        $this->partialMock(TocCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('createItemsAndAddtoFrontier');
        });
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $arr = [new TocItemDraft([])];
        app(Arachno::class)->captureTocFromDraftArray($pageCrawl, $arr);

        $this->assertTrue(true);
    }

    public function testCreateTocItem(): void
    {
        $this->partialMock(TocCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('createItem');
        });
        /** @var Doc */
        $doc = Doc::factory()->create();
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $pageCrawl->setDoc($doc);

        app(Arachno::class)->createTocItem($pageCrawl, new TocItemDraft([]));

        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $this->expectException(RuntimeException::class);
        app(Arachno::class)->createTocItem($pageCrawl, new TocItemDraft([]));
    }

    /**
     * @return Generator<TocItemDraft>
     */
    private function _getGenerator(): Generator
    {
        for ($i = 0; $i < 5; $i++) {
            yield new TocItemDraft([]);
        }
    }

    public function testFetchLink(): void
    {
        $html = '<html><body></body></html>';
        $this->partialMock(Fetch::class, function (MockInterface $mock) use ($html) {
            $mock->shouldReceive('fetchFromCacheOrUrl')->andReturn(new ContentCache(['response_body' => $html]));
        });

        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(), []);
        $this->assertInstanceOf(DomCrawler::class, app(Arachno::class)->fetchLink($pageCrawl, 'http://example.com'));
    }

    public function testCrawlTypes(): void
    {
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawl_type' => CrawlType::FOR_UPDATES->value]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id]);
        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, []);
        $this->assertTrue(app(Arachno::class)->crawlIsForUpdates($pageCrawl));

        $pageUrl->crawl?->update(['crawl_type' => CrawlType::FULL_CATALOGUE->value]);
        $this->assertTrue(app(Arachno::class)->crawlIsFullCatalogue($pageCrawl));

        $pageUrl->crawl?->update(['crawl_type' => CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value]);
        $this->assertTrue(app(Arachno::class)->crawlIsNewCatalogueWorkDiscovery($pageCrawl));

        $pageUrl->crawl?->update(['crawl_type' => CrawlType::FETCH_WORKS->value]);
        $this->assertTrue(app(Arachno::class)->crawlIsFetchWorks($pageCrawl));

        $pageUrl->crawl?->update(['crawl_type' => CrawlType::WORK_CHANGES_DISCOVERY->value]);
        $this->assertTrue(app(Arachno::class)->crawlIsWorkChangesDiscovery($pageCrawl));

        $pageUrl->crawl?->update(['crawl_type' => CrawlType::SEARCH->value]);
        $this->assertTrue(app(Arachno::class)->crawlIsSearch($pageCrawl));
    }

    public function testIndexForSearch()
    {
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawl_type' => CrawlType::SEARCH->value]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'crawl_type' => CrawlType::SEARCH->value]);
        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, []);

        $this->partialMock(SearchPageSave::class, function (MockInterface $mock) {
            $mock->shouldReceive('savePage')->andReturn(new SearchPage());
        });

        $searcHPage = app(Arachno::class)->indexForSearch($pageCrawl);
        $this->assertInstanceOf(SearchPage::class, $searcHPage);
    }

    public function testSetCrawlCookies()
    {
        /** @var Crawler $crawler */
        $crawler = Crawler::factory()->create();
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        $pageUrl = UrlFrontierLink::factory()->make(['crawl_id' => $crawl->id]);
        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, []);

        $cookies = new CookieJar(false, [
            [
                'Name' => 'test-cookie',
                'Value' => 'test-value',
                'Domain' => 'test-domain',
            ],
        ]);

        $pageCrawl->pageUrl = $pageUrl;

        $this->assertNull($crawl->refresh()->cookies);

        app(Arachno::class)->setCrawlCookies($pageCrawl, $cookies);

        $this->assertNotNull($crawl->refresh()->cookies);

        $cookies = $crawl->refresh()->cookies[0] ?? [];

        $this->assertSame('test-cookie', $cookies['Name'] ?? false);
        $this->assertSame('test-value', $cookies['Value'] ?? false);
    }
}
