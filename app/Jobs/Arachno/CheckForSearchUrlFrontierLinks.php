<?php

namespace App\Jobs\Arachno;

use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckForSearchUrlFrontierLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $links = UrlFrontierLink::uncrawled()
            ->crawlTypeSearch()
            ->limit(config('arachno.check_frontier_search_chunks'))
            ->get(['id', 'crawl_id']);

        foreach ($links as $frontierLink) {
            // ShouldBeUnique has issues, so we're using our own lock
            $lock = Cache::lock(config('arachno.crawl_url_job_lock') . $frontierLink->id, 7200);
            if (!$lock->get()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $countQueues = config('arachno.search_queues');
            // make sure that the links within a crawl are all placed on the same queue, so a source is not overloaded with simultaneous requests.
            $queueNumber = ($frontierLink->crawl_id % $countQueues) + 1;
            $queue = 'arachno-search-' . ((string) $queueNumber);
            CrawlUrl::dispatch($frontierLink->id)
                ->onQueue($queue);
        }
    }
}
