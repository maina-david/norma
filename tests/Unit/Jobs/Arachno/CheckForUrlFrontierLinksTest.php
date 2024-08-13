<?php

namespace Tests\Unit\Jobs\Arachno;

use App\Jobs\Arachno\CheckForUrlFrontierLinks;
use App\Jobs\Arachno\CrawlUrl;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CheckForUrlFrontierLinksTest extends TestCase
{
    public function testHandle(): void
    {
        Bus::fake();
        $job = new CheckForUrlFrontierLinks();
        $job->handle();
        Bus::assertNotDispatched(CrawlUrl::class);

        UrlFrontierLink::factory(2)->create();
        $job->handle();
        Bus::assertDispatched(CrawlUrl::class);
    }
}
