<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Arachno\UrlFrontierMeta;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Crawlers\CrawlerConfigFactory;
use App\Services\Arachno\Crawlers\DefaultCrawlerConfig;
use App\Services\Arachno\Frontier\CrawlerManager;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class CrawlerManagerTest extends TestCase
{
    public function testCreateCrawl(): void
    {
        $crawler = Crawler::factory()->create();
        $count = Crawl::count();
        $crawl = app(CrawlerManager::class)->createCrawl($crawler);
        $this->assertGreaterThan($count, Crawl::count());
    }

    public function testStartCrawl(): void
    {
        $this->partialMock(UrlFrontierLinkManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('addToFrontier')->once();
        });

        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        $manager = app(CrawlerManager::class);
        $crawl = $manager->createCrawl($crawler);
        $manager->startCrawl($crawl);

        $this->partialMock(CrawlerConfigFactory::class, function (MockInterface $mock) use ($crawler) {
            $config = new DefaultCrawlerConfig(app(Arachno::class), $crawler);
            $mock->shouldReceive('createConfigForCrawler')->andReturn($config);
        });

        $manager = app(CrawlerManager::class);
        $manager->startCrawl($crawl);
    }

    public function testCrawlUrl(): void
    {
        Bus::fake();
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id]);
        $contentCache = ContentCache::factory()->for($crawl)->create(['response_body' => $this->faker->randomHtml(1, 2)]);

        $this->mock(Fetch::class, function (MockInterface $mock) use ($contentCache) {
            $mock->shouldReceive('fetchPage')->andReturn($contentCache);
        });

        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id]);
        $frontierMeta = UrlFrontierMeta::factory()->create(['url_frontier_link_id' => $pageUrl->id, 'meta' => ['source_unique_id' => '12345', 'language_code' => 'eng', 'source_url' => 'http://example.com']]);
        $this->assertNull($pageUrl->crawled_at);

        app(CrawlerManager::class)->crawlUrl($pageUrl);
        $this->assertNotNull($pageUrl->crawled_at);

        // test a PDF crawl
        $contentCache = ContentCache::factory()->for($crawl)->create(['response_body' => null, 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $this->mock(Fetch::class, function (MockInterface $mock) use ($contentCache) {
            $mock->shouldReceive('fetchPage')->andReturn($contentCache);
        });

        app(CrawlerManager::class)->crawlUrl($pageUrl);
    }
}
