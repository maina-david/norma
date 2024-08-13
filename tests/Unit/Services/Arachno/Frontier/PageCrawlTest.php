<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Arachno\UrlFrontierMeta;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Frontier\PageCrawl;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class PageCrawlTest extends TestCase
{
    public function testGetJson(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create();
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => '{"test":"result"}', 'response_headers' => ['Content-Type' => ['application/json']]]);
        /** @var UrlFrontierLink */
        $pageUrlReferer = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'content_cache_id' => $contentCache->id]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'referer_id' => $pageUrlReferer->id]);

        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, [], $contentCache);

        $this->assertSame('result', $pageCrawl->getJson()['test']);

        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, [], $contentCache);
        $this->assertSame('result', $pageCrawl->getRefererJson()['test']);
    }

    public function testGetRefererDom(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create();
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => '<html><head></head><body><div></div></body></html>', 'response_headers' => ['Content-Type' => ['text/html']]]);
        /** @var UrlFrontierLink */
        $pageUrlReferer = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'content_cache_id' => $contentCache->id]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'referer_id' => $pageUrlReferer->id]);

        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, [], $contentCache);

        $crawler = $pageCrawl->getRefererDom();
        $this->assertInstanceOf(DomCrawler::class, $crawler);
    }

    public function testGetFinalRedirectedUrl(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create();
        $testUrl = 'http://example.com';
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => '<html><head></head><body><div></div></body></html>', 'response_headers' => ['X-Guzzle-Redirect-History' => [$testUrl]]]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id]);

        $pageCrawl = new PageCrawl(new DomCrawler(), $pageUrl, [], $contentCache);

        $result = $pageCrawl->getFinalRedirectedUrl();
        $this->assertSame($testUrl, $result);

        /** @var Doc */
        $doc = Doc::factory()->create();
        $pageCrawl->setDoc($doc);
        $this->assertSame($doc->id, $pageCrawl->getDoc()?->id);
    }

    public function testGetFrontierMeta(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        $title = 'Title';
        $uniqueId = '1234';
        $number = '4321';

        /** @var UrlFrontierLink */
        $refererRef = UrlFrontierLink::factory()->create();
        UrlFrontierMeta::factory()->create(['url_frontier_link_id' => $refererRef->id, 'meta' => ['title' => $title]]);

        /** @var UrlFrontierLink */
        $referer = UrlFrontierLink::factory()->create(['referer_id' => $refererRef->id]);
        UrlFrontierMeta::factory()->create(['url_frontier_link_id' => $referer->id, 'meta' => ['source_unique_id' => $uniqueId]]);

        /** @var UrlFrontierLink */
        $link = UrlFrontierLink::factory()->create(['referer_id' => $referer->id]);
        UrlFrontierMeta::factory()->create(['url_frontier_link_id' => $link->id, 'meta' => ['work_number' => $number]]);

        $pageCrawl = new PageCrawl(new DomCrawler(), $link, []);
        $docMeta = $pageCrawl->getFrontierMeta();
        $this->assertSame($title, $docMeta['title']);
        $this->assertSame($uniqueId, $docMeta['source_unique_id']);
        $this->assertSame($number, $docMeta['work_number']);
    }

    public function testGetSettingCss(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        $crawler->source?->update(['css' => '.css-from-source']);

        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(['crawl_id' => $crawl->id]), ['css' => 'somecss']);
        $css = $pageCrawl->getSettingCss();
        $this->assertStringContainsString('somecss', $css ?? '');
        $this->assertStringContainsString('.css-from-source', $css ?? '');
    }

    public function testGetTypes(): void
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        $pageCrawl = new PageCrawl(new DomCrawler(), new UrlFrontierLink(['crawl_id' => $crawl->id]), ['css' => 'somecss']);
        $pageCrawl->mimeType = 'application/json';
        $this->assertTrue($pageCrawl->isJson());
        $this->assertFalse($pageCrawl->isPdf());
        $pageCrawl->mimeType = 'application/pdf';
        $this->assertTrue($pageCrawl->isPdf());
        $this->assertFalse($pageCrawl->isDocx());

        $pageCrawl->mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $this->assertTrue($pageCrawl->isDocx());
    }

    public function testIsCatalogueStartUrl(): void
    {
        $url = 'https://example.com';
        /** @var CatalogueDoc */
        $catalogueDoc = CatalogueDoc::factory()->create(['start_url' => $url]);
        $link = new UrlFrontierLink(['url' => $url]);
        $pageCrawl = new PageCrawl(new DomCrawler(), $link, ['css' => 'somecss']);
        $this->assertFalse($pageCrawl->isCatalogueStartUrl());
        $link->catalogueDoc()->associate($catalogueDoc);
        $this->assertTrue($pageCrawl->isCatalogueStartUrl());
    }

    public function testProxyAndHttpSettings(): void
    {
        $link = new UrlFrontierLink(['url' => 'https://example.com']);
        $pageCrawl = new PageCrawl(new DomCrawler(), $link, []);
        $pageCrawl->setProxySettings(['provider' => 'scrapfly']);
        $pageCrawl->setHttpSettings(['method' => 'POST']);
        $this->assertTrue($pageCrawl->usesProxy());
        $this->assertSame('POST', $pageCrawl->getHttpSettings()['method']);
        $this->assertSame('scrapfly', $pageCrawl->getProxySettings()['provider']);
    }
}
