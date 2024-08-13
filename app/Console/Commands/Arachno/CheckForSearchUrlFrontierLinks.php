<?php

namespace App\Console\Commands\Arachno;

use App\Jobs\Arachno\CheckForSearchUrlFrontierLinks as CheckForSearchUrlFrontierLinksJob;
use Illuminate\Console\Command;

/**
 * This merely calls the job, so no need to cover in tests.
 *
 * @codeCoverageIgnore
 */
class CheckForSearchUrlFrontierLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arachno:check-url-frontier-search {--continue} {--sleep=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks whether there are any links in the Frontier that need to be crawled and adds them to the queue - specifically for the Search crawler';

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
                CheckForSearchUrlFrontierLinksJob::dispatch()->onQueue('arachno-frontier-checks');
                sleep($sleep);
            }
        } else {
            CheckForSearchUrlFrontierLinksJob::dispatch()->onQueue('arachno-frontier-checks');
        }

        return 0;
    }
}
