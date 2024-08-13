<?php

namespace App\Jobs\Corpus;

use App\Jobs\Middleware\Debounce;
use App\Models\Corpus\WorkExpression;
use App\Traits\UsesHighlighter;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdateWorkExpressionWordCount implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UsesHighlighter;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param WorkExpression $expression
     */
    public function __construct(protected WorkExpression $expression)
    {
    }

    /**
     * Retry the execution until a day is over.
     *
     * @return Carbon
     */
    public function retryUntil(): Carbon
    {
        return now()->addHours(2);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<array-key, mixed>
     */
    public function middleware(): array
    {
        return [
            new Debounce("word_count_{$this->expression->id}", 300, $this->timeout),
        ];
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Debounce::cleanUp("word_count_{$this->expression->id}");
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
        $this->expression->word_count = $this->getCount();
        $this->expression->save();
    }

    /**
     * Get the word count.
     *
     * @throws GuzzleException
     *
     * @return int
     */
    protected function getCount(): int
    {
        $content = '';

        for ($i = 1; $i <= $this->expression->volume; $i++) {
            $content .= $this->expression->getVolume($i);
        }

        if (!$content || strlen($content) < 8) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }

        $content = $this->highlighterClient()
            ->post('/word-count', ['json' => ['content' => $content]])
            ->getBody()
            ->getContents();

        return json_decode($content)->count ?? 0;
    }
}
