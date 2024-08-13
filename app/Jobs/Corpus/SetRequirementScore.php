<?php

namespace App\Jobs\Corpus;

use App\Http\Services\LibryoAI\Client;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SetRequirementScore implements ShouldQueue, ShouldBeUnique
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
    public int $uniqueFor = 610;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 30;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 650;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected TocItem $tocItem)
    {
        $this->onQueue('ai-classifier');
    }

    /**
     * Get the unique ID for the job.
     *
     * @return int
     */
    public function uniqueId(): int
    {
        return $this->tocItem->id;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Corpus\TocItemContentExtractor $extractor
     * @param \App\Http\Services\LibryoAI\Client           $client
     *
     * @throws Exception
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function handle(TocItemContentExtractor $extractor, Client $client): void
    {
        try {
            $content = $extractor->extractItem($this->tocItem);
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $content = html_to_text($content);
        $score = $client->classifyObligations($content);

        $this->tocItem->requirement_score = $score;
        $this->tocItem->save();
    }
}
