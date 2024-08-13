<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\Link;
use App\Models\Arachno\Source;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\TocCapture;
use App\Services\Arachno\Parse\TocItemDraft;
use Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class TocCaptureTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function setupCrawlerConfig()
    {
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();

        $pageUrl->crawl_id = $this->crawl?->id;
        $pageUrl->crawl()->associate($this->crawl);
        $pageUrl->url = 'http://example.com';
        $pageUrl->save();

        $toCache = [
            '/rav_9/index.html',
            '/kovanpg_9/index.html',
            '/test.html',
            '/test2.html',
            '/test3.html',
        ];

        foreach ($toCache as $cache) {
            ContentCache::factory()->create([
                'url' => $pageUrl->url . $cache,
                'crawl_id' => $this->crawl?->id,
                'response_body' => '',
            ]);
        }

        /** @var Source */
        $source = Source::factory()->create(['slug' => 'de-gesetze-im-internet']);
        $sourceUniqueId = Str::random(20);
        /** @var Doc */
        $doc = Doc::factory()->create(['uid' => '', 'source_id' => $source->id, 'source_unique_id' => $sourceUniqueId]);
        $doc->setUid()->save();

        return [$pageUrl, $doc];
    }

    public function testCapture(): void
    {
        $this->prep();
        [$pageUrl, $doc] = $this->setupCrawlerConfig();

        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, ['css' => '.injected-css { margin: 15px; }']);
        $pageCrawl->setDoc($doc);
        $tocQuery = new DomQuery(['type' => DomQueryType::CSS, 'query' => '#toc']);
        app(TocCapture::class)->capture($pageCrawl, $tocQuery);
        $afterCount = TocItem::count();
        $this->assertDatabaseHas((new TocItem())->getTable(), ['label' => 'Part 1. A relative link to another document']);
        $this->assertDatabaseHas((new Link())->getTable(), ['url' => $pageUrl->url . '/test3.html']);

        app(TocCapture::class)->capture($pageCrawl, $tocQuery);
        $this->assertEquals($afterCount, TocItem::count(), 'Failed to assert that toc items do not get created again when run a second time');

        $this->assertEquals(count($this->domCrawler->filter('#toc a')), $afterCount);
    }

    public function testCreateItemsAndAddtoFrontier(): void
    {
        $domCrawler = new DomCrawler();
        $json = '{"item": {"title": "Hello"}}';
        $domCrawler->addContent($json, 'application/json');
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);

        /** @var Crawl $crawl */
        $crawl = $crawler->createCrawl();
        $crawl->crawler()->associate($crawler);
        /** @var Doc $doc */
        $doc = Doc::factory()->create();
        $item = $doc->tocItems()->create([
            'source_unique_id' => 'testing',
            'crawl_id' => $crawl->id,
        ]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['crawl_id' => $crawl->id, 'url' => 'http://example.com', 'doc_id' => $doc->id]);
        $pageUrl->save();

        /** @var ContentCache */
        $contentCache = ContentCache::create(['response_body' => $json, 'response_headers' => ['Content-Type' => ['application/json']]]);
        $pageCrawl = new PageCrawl($domCrawler, $pageUrl, [], $contentCache);
        $tocQuery = new DomQuery(['type' => DomQueryType::CSS, 'query' => '#toc']);
        app(TocCapture::class)->createItemsAndAddtoFrontier($this->generateToCItems('testing'), $pageCrawl);
        $this->assertDatabaseMissing((new TocItem())->getTable(), ['label' => 'Part 1. A relative link to another document']);

        app(TocCapture::class)->createItemsAndAddtoFrontier($this->generateToCItems(), $pageCrawl);
        $this->assertDatabaseHas((new TocItem())->getTable(), ['label' => 'Part 1. A relative link to another document']);

        $this->assertDatabaseHas((new Link())->getTable(), ['url' => $pageUrl->url . '/test3.html']);
        $this->assertDatabaseHas((new UrlFrontierLink())->getTable(), ['url' => $pageUrl->url . '/test2.html']);
    }

    /**
     * @param mixed|null $sourceUnique
     *
     * @return Generator<TocItemDraft>
     */
    protected function generateToCItems($sourceUnique = null): Generator
    {
        $items = [
            new TocItemDraft(['source_unique_id' => $sourceUnique ?? Str::random(12), 'href' => '/test3.html', 'label' => 'Part 1. A relative link to another document',  'links' => [new UrlFrontierLink(['url' => '/test2.html', 'anchor_text' => 'text'])]]),
        ];

        foreach ($items as $item) {
            yield $item;
        }
    }

    public function testCreateItemWithSourceUniqueIdQuery(): void
    {
        $this->prep();
        [$pageUrl, $doc] = $this->setupCrawlerConfig();
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);

        $item = app(TocCapture::class)->createItem(new TocItemDraft(['href' => '/index.html', 'label' => 'A Heading', 'level' => 1, 'position' => 1, 'source_unique_id' => '1234']), $pageCrawl, $doc);
        $this->assertInstanceOf(TocItem::class, $item);
    }
}
