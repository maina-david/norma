<?php

namespace Tests\Unit\Services\Arachno\Crawlers;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Crawlers\DefaultCrawlerConfig;
use App\Services\Arachno\Crawlers\InvalidCrawlerConfigException;
use App\Services\Arachno\Parse\DomQuery;
use Tests\TestCase;
use Throwable;

class DefaultCrawlerConfigTest extends TestCase
{
    private function createConfig(): DefaultCrawlerConfig
    {
        /** @var Crawler */
        $crawler = Crawler::factory()->for(Source::factory()->create(['css' => 'p { text-align: left; }']))->create(['slug' => 'default']);

        return new DefaultCrawlerConfig(app(Arachno::class), $crawler);
    }

    public function testSetConfig(): void
    {
        $this->expectException(InvalidCrawlerConfigException::class);
        try {
            $this->createConfig()->setSettings(['start_urls' => 'http://www.google.com']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('start_urls has to be an array', $e);
            throw $e;
        }
    }

    public function testSetConfig8(): void
    {
        $this->expectException(InvalidCrawlerConfigException::class);
        try {
            $this->createConfig()->setSettings(['css_exclude_selectors' => '']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('css_exclude_selectors has to be an array', $e);
            throw $e;
        }
    }

    public function testSetConfig17(): void
    {
        $this->expectException(InvalidCrawlerConfigException::class);
        try {
            $this->createConfig()->setSettings(['css' => []]);
        } catch (Throwable $e) {
            $this->assertStringContainsString('css has to be a string or null', $e);
            throw $e;
        }
    }

    public function testGetters(): void
    {
        $config = $this->createConfig();

        $url = 'http://example.com';
        $config->setSettings(['start_urls' => ['type_' . CrawlType::GENERAL->value => [new UrlFrontierLink(['url' => $url])]]]);

        $urls = $config->getStartUrls(CrawlType::GENERAL);
        $this->assertSame($url, $urls[0]->url);

        $cookies = ['test' => 'cookie'];
        $config->setSettings(['default_cookies' => $cookies]);
        $this->assertSame($cookies['test'], $config->getDefaultCookies()['test']);

        $agent = 'User Agent';
        $config->setSettings(['user_agent' => $agent]);
        $this->assertSame($agent, $config->getUserAgent());

        $expected = 800;
        $config->setSettings(['throttle_requests' => $expected]);
        $this->assertSame($expected, $config->getThrottleRequests());

        $expected = false;
        $config->setSettings(['verify_ssl' => $expected]);
        $this->assertSame($expected, $config->getVerifySsl());

        $expected = 'text-align:center;';
        $config->setSettings(['css' => $expected]);
        $this->assertStringContainsString($expected, $config->getCss() ?? '');

        $expected = 'text-align';
        $config->setSettings(['css_exclude_selectors' => [$expected]]);
        $this->assertStringContainsString($expected, $config->getExcludedCssSelectors()[0]);

        $expected = false;
        $config->setSettings(['delete_if_unused' => $expected]);
        $this->assertSame($expected, $config->getDeleteIfUnused());
    }

    public function testArrayOfQueries(): void
    {
        $config = $this->createConfig();
        $arr = $config->arrayOfQueries([['type' => DomQueryType::CSS, 'query' => '.class']]);

        $this->assertInstanceOf(DomQuery::class, $arr[0]);
    }
}
