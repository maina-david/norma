<?php

namespace App\Jobs\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunCrawler implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $crawlerId, protected ?int $crawlType = null)
    {
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return static::class . '_' . $this->crawlerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Crawler */
        $crawler = Crawler::findOrFail($this->crawlerId);
        $crawlType = !is_null($this->crawlType) ? CrawlType::from($this->crawlType) : ($crawler->isForUpdates() ? CrawlType::FOR_UPDATES : CrawlType::GENERAL);

        $crawler->createAndStartCrawl($crawlType);
    }
}
