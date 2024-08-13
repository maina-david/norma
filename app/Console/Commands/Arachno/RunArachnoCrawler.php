<?php

namespace App\Console\Commands\Arachno;

use App\Jobs\Arachno\RunCrawler;
use Illuminate\Console\Command;

/**
 * This merely calls the job, so no need to cover in tests.
 *
 * @codeCoverageIgnore
 */
class RunArachnoCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arachno:run-crawler {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts a crawl for the given crawler';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var int $crawler */
        $crawler = $this->argument('id');

        RunCrawler::dispatch($crawler)->onQueue('arachno');

        return 0;
    }
}
