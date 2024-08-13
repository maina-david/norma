<?php

namespace App\Jobs\Arachno;

use App\Actions\Arachno\Doc\HandleCrawlComplete;
use App\Models\Arachno\UrlFrontierLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckCrawlComplete implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // /**
    //  * The number of seconds after which the job's unique lock will be released.
    //  *
    //  * @var int
    //  */
    // public $uniqueFor = 150;

    /**
     * @param int $docId
     * @param int $crawlId
     * @param int $attempt
     *
     * @return void
     */
    public function __construct(protected int $docId, protected int $crawlId, protected int $attempt = 0)
    {
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return static::class . '_' . $this->crawlId . '_' . $this->docId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $uncrawledLinksExist = UrlFrontierLink::where('crawl_id', $this->crawlId)
            ->where('doc_id', $this->docId)
            ->uncrawled()
            ->exists();

        // if there are uncrawled links let's check again in 3 minutes
        if ($uncrawledLinksExist && $this->attempt < config('arachno.check_crawl_complete_attempts')) {
            $minutes = $this->attempt > 4 ? 30 : 3;
            $attempt = $this->attempt + 1;
            static::dispatch($this->docId, $this->crawlId, $attempt)
                ->delay(now()->addMinutes($minutes))
                ->onQueue('arachno-checks');

            return;
        }

        HandleCrawlComplete::run($this->docId);
    }
}
