<?php

namespace App\Jobs\Arachno;

use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckForUrlFrontierLinks implements ShouldQueue
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
            ->crawlTypeNotSearch()
            ->limit(config('arachno.check_frontier_chunks'))
            ->get(['id']);

        foreach ($links as $frontierLink) {
            // ShouldBeUnique has issues, so we're using our own lock
            $lock = Cache::lock(config('arachno.crawl_url_job_lock') . $frontierLink->id, 7200);
            if (!$lock->get()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            CrawlUrl::dispatch($frontierLink->id)
                ->onQueue('arachno');
        }
    }
}
