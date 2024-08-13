<?php

namespace App\Jobs\Arachno;

use App\Actions\Arachno\Doc\HandleCrawlComplete;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\CrawlerManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Throwable;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * @codeCoverageIgnore
 */
class CrawlUrl implements ShouldQueue // , ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    // /**
    //  * The number of seconds after which the job's unique lock will be released.
    //  *
    //  * @var int
    //  */
    // public $uniqueFor = 300;

    /**
     * @param int $urlId
     */
    public function __construct(protected int $urlId)
    {
    }

    // public function uniqueId(): string
    // {
    //     return static::class . '_' . $this->urlId;
    // }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CrawlerManager $crawlerManager)
    {
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::find($this->urlId);
        $pageUrl->load(['frontierMeta', 'refererLink.frontierMeta', 'refererLink.refererLink.frontierMeta']);
        $crawlerManager->crawlUrl($pageUrl);
        $this->releaseLock();
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::find($this->urlId);
        if ($pageUrl->catalogue_doc_id && $pageUrl->catalogueDoc) {
            app(HandleCrawlComplete::class)->stopFetchStarted($pageUrl->catalogueDoc);
        }

        $trace = array_map(function ($tr) {
            /** @var array<string, mixed> $tr */
            //            return $tr['file'] . '(' . $tr['line'] . ')';
            return $tr['file'] ?? $tr['class'] . '(' . ($tr['line'] ?? $tr['function']) . ')';
        }, $exception->getTrace());

        $pageUrl->frontier_log = ($pageUrl->frontier_log ?? '') . PHP_EOL . $exception->getMessage() . PHP_EOL . implode(PHP_EOL, $trace);
        $pageUrl->save();
        $this->releaseLock();
    }

    protected function releaseLock(): void
    {
        // ShouldBeUnique has issues, so we're using our own lock
        Cache::lock(config('arachno.crawl_url_job_lock') . $this->urlId)->forceRelease();
    }
}
