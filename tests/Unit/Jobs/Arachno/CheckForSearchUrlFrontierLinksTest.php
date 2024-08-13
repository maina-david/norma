<?php

namespace Tests\Unit\Jobs\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Jobs\Arachno\CheckForSearchUrlFrontierLinks;
use App\Jobs\Arachno\CrawlUrl;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CheckForSearchUrlFrontierLinksTest extends TestCase
{
    public function testHandle(): void
    {
        Bus::fake();
        $job = new CheckForSearchUrlFrontierLinks();
        $job->handle();
        Bus::assertNotDispatched(CrawlUrl::class);

        UrlFrontierLink::factory(2)->create(['crawl_type' => CrawlType::SEARCH->value]);
        $job->handle();
        Bus::assertDispatched(CrawlUrl::class);
    }
}
