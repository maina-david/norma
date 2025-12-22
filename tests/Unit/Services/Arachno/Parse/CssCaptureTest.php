<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\CssCapture;
use App\Services\Arachno\Parse\CssProcessor;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CssCaptureTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function testCapture(): void
    {
        Storage::fake('local');

        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();

        // so it doesn't try fetch the URL
        /** @var ContentCache */
        $cache = ContentCache::factory([
            'url' => 'http://example.com/css/style.css',
            'response_body' => 'h1 { margin-bottom: 20px; }',
        ])->create();
        /** @var Crawl */
        $crawl = $this->crawlerConfig->crawler->createCrawl();

        $cache->crawl_id = $crawl->id;
        $cache->setUid();
        $cache->save();
        $pageUrl->crawl_id = $crawl->id;
        $pageUrl->url = 'http://example.com';
        $pageUrl->save();

        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, ['css' => '.injected-css { margin: 15px; }']);
        $capturedDom = app(CssCapture::class)->capture($pageCrawl, fn ($css) => $css);

        $testCrawler = new DomCrawler($this->html);
        $this->assertNull(
            $capturedDom->filter('head style')->getNode(0),
            'Failed to assert that the style tags have been removed'
        );
        $this->assertTrue(
            count($testCrawler->filter('head link')) === count($capturedDom->filter('head link')) - 2,
            'Failed asserting that head has one two link tags'
        );
    }

    public function testFollowStylesheetImports(): void
    {
        Storage::fake('local');

        $this->prep();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();
        $importedCss = 'h1{margin-bottom:20px;}';
        $contentCache = ContentCache::factory([
            'url' => 'http://example.com/css/style.css',
            'response_body' => $importedCss,
        ])->create();
        $this->mock(Fetch::class, function (MockInterface $mock) use ($contentCache) {
            $mock->shouldReceive('fetchFromCacheOrUrl')->andReturn($contentCache);
        });

        $css = '@import "http://example.com/css/style.css";';
        $crawler = new DomCrawler('<html><head><style>' . $css . '</style></head></html>');
        $pageCrawl = new PageCrawl($crawler, $pageUrl, []);
        app(CssCapture::class)->followStylesheetImports($pageCrawl);

        $this->assertTrue(
            count($crawler->filter('head link')) > 0,
            'Failed to assert that a link has been created'
        );
        /** @var DOMElement */
        $n = $crawler->filter('head link')->getNode(0);
        $this->assertStringContainsString(
            'stylesheet',
            $n->getAttribute('rel')
        );

        /** @var DOMElement */
        $n = $crawler->filter('head link')->getNode(0);
        $this->assertStringContainsString(
            app(ContentResourceStore::class)->getPath(sha1('.' . CssProcessor::CSS_NORMA_FULL_TEXT_CLASS . ' ' . $importedCss)),
            $n->getAttribute('href')
        );
    }

    public function testInjectCss(): void
    {
        $this->prep();
        $capturedDom = app(CssCapture::class)->injectCss($this->domCrawler, '.injected-css { margin: 15px; }');

        $this->assertStringContainsString('.injected-css', $capturedDom->filter('head')->outerHtml());
    }

    public function testAddCssLink(): void
    {
        $domCrawler = new DomCrawler();
        $html = '<html><head></head><body></body></html>';
        $domCrawler->addContent($html);

        $href = 'http://example.com/style.css';
        $capturedDom = app(CssCapture::class)->addCssLink($domCrawler, $href);

        /** @var DOMElement */
        $n = $capturedDom->filter('head link')->getNode(0);
        $this->assertTrue($n->getAttribute('href') === $href);
    }
}
