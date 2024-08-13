<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use App\Services\Arachno\Arachno;
use App\Services\Arachno\Crawlers\DefaultCrawlerConfig;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

abstract class AbstractDomParseTests extends TestCase
{
    protected DomCrawler $domCrawler;
    protected string $html;
    protected DefaultCrawlerConfig $crawlerConfig;
    protected ?Crawl $crawl;

    protected function prep(): void
    {
        $this->domCrawler = new DomCrawler();
        $this->html = (string) file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data.html');
        $this->domCrawler->addContent($this->html);

        /** @var Crawler */
        $crawler = Crawler::factory()->create(['slug' => 'test']);
        $this->crawlerConfig = new DefaultCrawlerConfig(app(Arachno::class), $crawler);
        $this->crawl = $crawler->createCrawl();

        $this->crawl?->crawler()->associate($crawler);
    }
}
