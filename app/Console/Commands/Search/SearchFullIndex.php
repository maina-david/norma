<?php

namespace App\Console\Commands\Search;

use App\Jobs\Search\Elastic\SearchFullIndexJob;
use Illuminate\Console\Command;

class SearchFullIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:full-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a full index on all documents - careful!';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SearchFullIndexJob::dispatch()->onQueue('long-running');
    }
}
