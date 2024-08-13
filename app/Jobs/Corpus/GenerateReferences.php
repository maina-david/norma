<?php

namespace App\Jobs\Corpus;

use App\Actions\Corpus\Reference\UpdateReferenceClosures;
use App\Jobs\Middleware\Debounce;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateReferences implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10800;

    /**
     * Create a new job instance.
     *
     * @param WorkExpression $expression
     */
    public function __construct(protected WorkExpression $expression)
    {
        $this->queue = 'long-running';
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @codeCoverageIgnore
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [self::class, sprintf('%s:%d', WorkExpression::class, $this->expression->id)];
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<array-key, mixed>
     */
    public function middleware(): array
    {
        return [
            new Debounce($this->debounceKey(), 60, $this->timeout, false),
        ];
    }

    /**
     * Get the debounce key.
     *
     * @return string
     */
    protected function debounceKey(): string
    {
        return "reference_generation_{$this->expression->id}";
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
        Debounce::cleanUp($this->debounceKey());
    }

    /**
     * Execute the job.
     *
     * @throws Throwable
     *
     * @return void
     */
    public function handle(): void
    {
        $firstTime = !Reference::where('work_id', $this->expression->work_id)
            ->typeCitation()
            ->exists();

        $jobs = [];

        /** @var WorkExpression $exp */
        $exp = WorkExpression::whereKey($this->expression)->first(['volume']);

        for ($i = 1; $i <= $exp->volume; $i++) {
            $jobs[] = new GenerateReferencesFromVolume($this->expression, $i, $firstTime);
        }

        $debounceKey = $this->debounceKey();
        $work = $this->expression->work;

        $this->expression->load(['work']);

        $batch = Bus::batch($jobs)
            ->then(function () use ($work) {
                // @codeCoverageIgnoreStart
                Bus::chain([
                    new UpdateReferenceParents($work),
                    new UpdateCitationPositions($work),
                    UpdateReferenceClosures::makeJob($work->id),
                ])->onQueue('long-running')->dispatch();
                // @codeCoverageIgnoreEnd
            })
            ->finally(fn () => Debounce::cleanUp($debounceKey))
            ->onQueue('long-running')
            ->dispatch();

        /** @var int $expiry */
        $expiry = config('cache-keys.corpus.generate_citations.expiry');

        Cache::put($this->expression->citationsBatchCacheKey(), $batch->id, now()->addMinutes($expiry));
    }
}
