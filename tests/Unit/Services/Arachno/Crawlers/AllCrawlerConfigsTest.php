<?php

namespace Tests\Unit\Services\Arachno\Crawlers;

use App\Models\Arachno\Crawler;
use App\Services\Arachno\Crawlers\CrawlerConfigFactory;
use App\Services\Arachno\Crawlers\CrawlerConfigInterface;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AllCrawlerConfigsTest extends TestCase
{
    private function getArachnoConfigs(): array
    {
        $arr = [];
        foreach (config('arachno_configs') as $code => $configs) {
            foreach ($configs as $slug => $className) {
                $arr[$slug] = $className;
            }
        }

        return $arr;
    }

    public function testAllCrawlerConfigsCanBeInitialised(): void
    {
        Http::fake();

        $configs = $this->getArachnoConfigs();
        foreach ($configs as $slug => $className) {
            $crawler = Crawler::factory(['slug' => $slug])->make();
            $crawlerConfig = app(CrawlerConfigFactory::class)->createConfigForCrawler($crawler);

            $this->assertInstanceOf(CrawlerConfigInterface::class, $crawlerConfig);
        }
    }
}
