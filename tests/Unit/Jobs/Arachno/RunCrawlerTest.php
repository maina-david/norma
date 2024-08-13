<?php

namespace Tests\Unit\Jobs\Arachno;

use App\Jobs\Arachno\RunCrawler;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\Crawler;
use Tests\TestCase;

class RunCrawlerTest extends TestCase
{
    public function testHandle(): void
    {
        $crawler = Crawler::factory()->create();
        $job = new RunCrawler($crawler->id);
        $crawlsCount = Crawl::count();
        $job->handle();
        $this->assertGreaterThan($crawlsCount, Crawl::count());
        $this->assertSame(RunCrawler::class . '_' . $crawler->id, $job->uniqueId());
    }
}
