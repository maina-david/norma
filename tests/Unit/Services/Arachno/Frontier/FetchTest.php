<?php

namespace Tests\Unit\Services\Arachno\Frontier;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Crawlers\DefaultCrawlerConfig;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class FetchTest extends TestCase
{
    private function mockClient(int $statusCode = 200, string $body = '', array $headers = []): ClientInterface
    {
        /** @var MockInterface $responseMock */
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->allows(['getBody' => $body, 'getHeaders' => $headers, 'getStatusCode' => $statusCode]);
        /** @var MockInterface $mockClient */
        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->allows([
            'request' => $responseMock,
        ]);

        /** @var ClientInterface $mockClient */
        return $mockClient;
    }

    public function testFetchFromCacheOrUrl(): void
    {
        $mockClient = $this->mockClient();
        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        /** @var Crawl */
        $crawl = Crawl::factory()->create(['crawler_id' => $crawler->id, 'cookies' => [['Name' => 'test', 'Value' => 'test-cookie', 'Domain' => 'example.com']]]);
        $url = 'http://example.com';
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->create(['crawl_id' => $crawl->id, 'url' => $url]);

        $config = new DefaultCrawlerConfig(app(Arachno::class), $crawler);
        $config->setSettingsItem('throttle_requests', 10);
        $pageCrawl = new PageCrawl(new DomCrawler('', $url), $pageUrl, ['throttle_requests' => 10]);
        $pageCrawl->setHttpSettings(['options' => ['verify' => false]]);
        $fetch = app(Fetch::class);
        $fetch->setHttpClient($mockClient);

        $count = ContentCache::count();
        $cached = $fetch->fetchPage($pageCrawl);
        $this->assertGreaterThan($count, ContentCache::count());
        $cached?->delete();

        $count = ContentCache::count();
        $cached = $fetch->fetchPage($pageCrawl);
        $this->assertGreaterThan($count, ContentCache::count());
        $cached?->delete();

        $count = ContentCache::count();
        $cached = $fetch->fetchPage($pageCrawl);
        $this->assertGreaterThan($count, ContentCache::count());
        $cached?->delete();
    }

    public function testFetch(): void
    {
        $mockClient = $this->mockClient(301);
        $fetch = app(Fetch::class);
        $fetch->setHttpClient($mockClient);

        $url = 'http://example.com';

        $response = $fetch->fetch('GET', $url);

        $this->assertNull($response);
    }
}
