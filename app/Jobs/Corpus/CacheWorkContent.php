<?php

namespace App\Jobs\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Jobs\Middleware\Debounce;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Corpus\WorkExpressionContentManager;
use App\Services\Storage\WorkStorageProcessor;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CacheWorkContent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10800;

    /**
     * Create a new job instance.
     *
     * @param Work $work
     */
    public function __construct(protected Work $work)
    {
        $this->queue = 'long-running';
    }

    /**
     * Retry the execution until a day is over.
     *
     * @return Carbon
     */
    public function retryUntil(): Carbon
    {
        return now()->addDay();
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<array-key, mixed>
     */
    public function middleware(): array
    {
        return [
            new Debounce("content_cache_trigger_{$this->work->id}", 60, $this->timeout),
        ];
    }

    /**
     * Handle a job failure.
     *
     * @codeCoverageIgnore
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Debounce::cleanUp("content_cache_trigger_{$this->work->id}");
    }

    /**
     * Execute the job.
     *
     * @throws GuzzleException
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->work->active_work_expression_id) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var WorkExpression $expression */
        $expression = WorkExpression::with(['convertedDocument'])->findOrFail($this->work->active_work_expression_id);

        $invalid = app(WorkExpressionContentManager::class)->validateVolumeMarkers($expression);

        if (!empty($invalid)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $highlighter = new VolumeHighlighter();

        $this->cacheContent($highlighter);

        //        PublishReferencesToFabric::dispatch($this->work);
    }

    /**
     * Cache the work content.
     *
     * @param VolumeHighlighter $highlighter
     *
     * @throws GuzzleException
     *
     * @return void
     */
    public function cacheContent(VolumeHighlighter $highlighter): void
    {
        $processor = new WorkStorageProcessor();

        $this->work->load('activeExpression');
        /** @var WorkExpression $expression */
        $expression = $this->work->activeExpression;

        for ($i = 1; $i <= $expression->volume; $i++) {
            $content = $highlighter->highlightSync($expression, $i);

            $processor->storeCachedWorkVolume($this->work, $i, $content);

            $this->updateReferenceContentForVolume($highlighter, $i);
        }

        $this->work->content_cache = null;
        $this->work->save();
    }

    /**
     * Update the content of the references for the given volume in the work.
     *
     * @param VolumeHighlighter $highlighter
     * @param int               $volume
     *
     * @return void
     */
    public function updateReferenceContentForVolume(VolumeHighlighter $highlighter, int $volume): void
    {
        $references = $this->work
            ->references()
            ->where('volume', $volume)
            ->where('type', ReferenceType::citation()->value)
            ->orderBy('start', 'ASC')
            ->orderBy('level', 'ASC')
            ->with([
                'refSelector:reference_id,selectors',
                'htmlContent:reference_id,cached_content',
            ])
            ->select(['id'])
            ->get();

        $content = $highlighter->referencesContent($this->work, $volume, $references, true);

        $references->each(function ($reference) use ($content) {
            if (!empty($response = $content->get($reference->id))) {
                ReferenceContent::updateOrCreate(['reference_id' => $reference->id], [
                    'reference_id' => $reference->id,
                    'cached_content' => $response,
                ]);
            }
        });
    }
}
