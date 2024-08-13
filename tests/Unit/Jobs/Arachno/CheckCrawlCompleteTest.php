<?php

namespace Tests\Unit\Jobs\Arachno;

use App\Jobs\Arachno\CheckCrawlComplete;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Frontier\DocHashGenerator;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckCrawlCompleteTest extends TestCase
{
    public function testHandle(): void
    {
        // first check that the job is queued again
        Bus::fake();
        $doc = Doc::factory()->create(['source_unique_id' => '123']);
        $crawl = Crawl::factory()->create();
        $links = UrlFrontierLink::factory(2)->create(['crawl_id' => $crawl->id, 'doc_id' => $doc->id]);

        $job = new CheckCrawlComplete($doc->id, $crawl->id);
        $job->handle();

        Bus::assertDispatched(CheckCrawlComplete::class);

        // ok now let's test that the setHash is called once a crawl is complete
        $this->mock(DocHashGenerator::class, function (MockInterface $mock) {
            $mock->shouldReceive('setHash');
        });
        $doc2 = Doc::factory()->create(['source_unique_id' => '123']);
        $this->travelTo(now()->subMinutes(10));
        $crawl = Crawl::factory()->create();
        UrlFrontierLink::all()->each(fn ($l) => $l->update(['crawled_at' => now()]));

        $job = new CheckCrawlComplete($doc2->id, $crawl->id);
        $job->handle();
    }
}
