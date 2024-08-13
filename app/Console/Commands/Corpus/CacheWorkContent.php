<?php

namespace App\Console\Commands\Corpus;

use App\Enums\System\ProcessingJobType;
use App\Jobs\Corpus\CacheWorkContent as CacheWorkContentJob;
use App\Models\Corpus\Work;
use App\Models\System\ProcessingJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class CacheWorkContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corpus:cache-work-content {work?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the cached content for the work.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var string|null $work */
        $work = $this->argument('work');

        if ($work) {
            CacheWorkContentJob::dispatch(Work::findOrFail($work));

            $this->info('Added to the queue.');

            return 0;
        }

        ProcessingJob::where('job_type', ProcessingJobType::cacheHighlights()->value)
            ->where('available_at', '<=', Carbon::now())
            ->chunk(5, function ($jobs) {
                foreach ($jobs as $job) {
                    $this->info('Caching highlights of: ' . $job->work_id);

                    /** @var Work|null $work */
                    $work = Work::has('activeExpression')->find($job->work_id);

                    if ($work) {
                        CacheWorkContentJob::dispatch($work);

                        $this->info('Added to the queue.');

                        ProcessingJob::where('job_type', ProcessingJobType::cacheHighlights()->value)
                            ->where('work_id', $work->id)
                            ->delete();
                    }
                }
            });

        return 0;
    }
}
