<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\PageCapture;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\DomCrawler\Crawler;

class PageCaptureTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function testCapture(): void
    {
        $this->prep();
        $headQueries = [new DomQuery([
            'type' => DomQueryType::CSS,
            'source' => DomQuerySource::DOM,
            'query' => 'head meta, head link, head title',
        ])];
        $bodyQueries = [new DomQuery([
            'type' => DomQueryType::XPATH,
            'source' => DomQuerySource::DOM,
            'query' => '//div[@id="content"]',
        ])];

        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();

        $crawl = $this->crawlerConfig->crawler->createCrawl();
        /** @var ContentCache */
        $cache = ContentCache::factory()->create(['url' => 'http://example.com/css/style.css', 'response_body' => 'h1 { margin-bottom: 20px; }', 'crawl_id' => $crawl?->id]);
        /** @var ContentCache */
        $cache = ContentCache::factory()->create(['url' => 'http://example.com/test.jpg', 'response_body' => '...', 'crawl_id' => $crawl?->id]);
        // use contentcache with response_headers with a redirected URL to test creating of two links in the `store` method
        $contentCache = ContentCache::factory()->create(['url' => 'http://example.com', 'response_body' => '...', 'crawl_id' => $crawl?->id, 'response_headers' => ['x-guzzle-redirect-history' => ['http://example.com/redirected']]]);

        $cache->crawl_id = $crawl?->id;
        $cache->setUid();
        $cache->save();
        $pageUrl->crawl_id = $crawl?->id;
        $pageUrl->url = 'http://example.com';
        $pageUrl->save();
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, [], $contentCache);
        /** @var Doc */
        $doc = Doc::factory()->create();
        $pageCrawl->setDoc($doc);

        $capturedDom = app(PageCapture::class)->capture($pageCrawl, $bodyQueries, $headQueries, [], null, fn ($crawler) => $crawler);

        $testCrawler = new Crawler($this->html);
        $this->assertEquals(
            $testCrawler->filter('#content')->getNode(0)->nodeName,
            $capturedDom->filter('#content')->getNode(0)->nodeName
        );
        $this->assertCount(
            count($testCrawler->filter('#content')),
            $capturedDom->filter('#content')
        );

        $this->assertEquals(
            $testCrawler->filter('head title')->outerHtml(),
            $capturedDom->filter('head title')->outerHtml()
        );

        /** @var DOMElement */
        $n = $capturedDom->filter('head link')->getNode(0);
        $this->assertStringContainsString(
            ContentResourceStore::PATH_PREFIX,
            $n->getAttribute('href'),
        );

        $testContent = '<div class="chunk1"></div><div class="chunk2"></div>';
        $testContent2 = '<div class="chunk3"></div><div class="chunk4"></div>';

        $bodyQueries = [new DomQuery([
            'type' => DomQueryType::FUNC,
            'query' => function (PageCrawl $pageCrawl) use ($testContent) {
                return $testContent;
            },
        ]),
            new DomQuery([
                'type' => DomQueryType::FUNC,
                'query' => function (PageCrawl $pageCrawl) use ($testContent2) {
                    return (new Crawler($testContent2))->filter('body')->children();
                },
            ]), ];

        $capturedDom = app(PageCapture::class)->capture($pageCrawl, $bodyQueries);
        $this->assertStringContainsString($testContent, $capturedDom->outerHtml());
        $this->assertStringContainsString($testContent2, $capturedDom->outerHtml());
    }

    public function testRemoveContent(): void
    {
        $domCrawler = new Crawler();
        $removeThis = 'remove-this';
        $keepThis = 'keep-this';
        $html = '<html><head></head><body><script type="text/javascript">alert("this should be removed")</script><div class="' . $removeThis . '"></div><div class="' . $keepThis . '"></div><div class="' . $removeThis . '"></div></body></html>';
        $domCrawler->addContent($html);
        $queries = [];
        $queries[] = new DomQuery([
            'type' => DomQueryType::CSS,
            'query' => '.' . $removeThis,
        ]);
        $this->assertCount(1, $domCrawler->filter('script'));
        $capturedDom = app(PageCapture::class)->removeContent($queries, $domCrawler);

        $this->assertCount(0, $capturedDom->filter('.' . $removeThis));
        $this->assertCount(1, $capturedDom->filter('.' . $keepThis));
        $this->assertCount(0, $capturedDom->filter('script'));
    }
}
