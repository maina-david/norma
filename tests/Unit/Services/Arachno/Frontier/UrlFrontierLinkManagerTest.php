<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Services\Arachno\Parse\DocMetaDto;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class UrlFrontierLinkManagerTest extends TestCase
{
    public function testAddToFrontier(): void
    {
        Bus::fake();
        $url = 'http://example.com';
        $uri = new Uri($url);
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'crawl_type' => CrawlType::GENERAL->value]);
        $addedUrl = app(UrlFrontierLinkManager::class)->addToFrontier($uri, $pageUrl);

        $this->assertSame($url, $addedUrl->url);

        $url = 'http://example2.com';
        $uri = new Uri($url);
        // different crawl should result in update rather than save
        $pageUrl = UrlFrontierLink::factory()->create(['url' => $url]);
        $pageUrl->setUid(UrlFrontierLink::hashUid($url))->save();
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'url' => $url, 'crawl_type' => CrawlType::GENERAL->value]);
        app(UrlFrontierLinkManager::class)->addToFrontier($uri, $pageUrl);
        $this->assertSame($url, $pageUrl->url);
    }

    public function testAddFromPageCrawl(): void
    {
        Bus::fake();

        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        $url = 'http://example.com/testing';
        $url2 = 'http://example2.com';
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'url' => $url, 'crawl_type' => CrawlType::GENERAL->value]);
        /** @var UrlFrontierLink */
        $pageUrl2 = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'url' => $url2, 'crawl_type' => CrawlType::GENERAL->value]);
        $pageCrawl = new PageCrawl(new DomCrawler(''), $pageUrl, []);
        $pageUrl2->_metaDto = new DocMetaDto(['source_unique_id' => '1234', 'title' => 'test']);
        $added = app(UrlFrontierLinkManager::class)->addFromPageCrawl($pageCrawl, $url, $pageUrl2);
        $this->assertSame($url, $added->url);
    }

    public function testReplaceVariables(): void
    {
        $url = app(UrlFrontierLinkManager::class)->replaceVariables('https://example.com/{{NOW:Y}}/updates');
        $this->assertSame(sprintf('https://example.com/%s/updates', now()->format('Y')), $url);

        $url = app(UrlFrontierLinkManager::class)->replaceVariables('https://example.com/{{NOW:Y}}/{{NOW:m}}/{{NOW:d}}');
        $this->assertSame(sprintf('https://example.com/%s/%s/%s', now()->format('Y'), now()->format('m'), now()->format('d')), $url);

        // lower case variable shouldn't replace anything
        $url = app(UrlFrontierLinkManager::class)->replaceVariables('https://example.com/{{now:Y}}/{{now:m}}/{{now:d}}');
        $this->assertSame('https://example.com/{{now:Y}}/{{now:m}}/{{now:d}}', $url);

        // only one bracket shouldn't match
        $url = app(UrlFrontierLinkManager::class)->replaceVariables('https://example.com/{now:Y}');
        $this->assertSame('https://example.com/{now:Y}', $url);
    }

    public function testAddMetaToLink(): void
    {
        $pageUrl = UrlFrontierLink::factory()->create();
        $title = 'A Title';
        $meta = app(UrlFrontierLinkManager::class)->addMetaToLink($pageUrl, new DocMetaDto(['title' => $title]));
        $this->assertSame($title, $meta->meta['title']);
    }
}
