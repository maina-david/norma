<?php

namespace App\Console\Commands\Arachno;

use App\Jobs\Arachno\CheckForUrlFrontierLinks as CheckForUrlFrontierLinksJob;
use Illuminate\Console\Command;

/**
 * This merely calls the job, so no need to cover in tests.
 *
 * @codeCoverageIgnore
 */
class CheckForUrlFrontierLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arachno:check-url-frontier {--continue} {--sleep=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks whether there are any links in the Frontier that need to be crawled and adds them to the queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // continually
        if ($this->option('continue')) {
            $sleep = (int) $this->option('sleep');
            // @phpstan-ignore-next-line
            while (true) {
                CheckForUrlFrontierLinksJob::dispatch()->onQueue('arachno-frontier-checks');
                sleep($sleep);
            }
        } else {
            CheckForUrlFrontierLinksJob::dispatch()->onQueue('arachno-frontier-checks');
        }

        return 0;
    }
}
