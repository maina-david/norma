<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class TerminateHorizon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:terminate-pid {pid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate the Horizon process that is of the given PID';

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
        // php artisan horizon:terminate-pid "$(ps aux | grep -E "artisan horizon$" | awk '{print $2}')"
        /** @var string $processId */
        $processId = $this->argument('pid');

        posix_kill((int) $processId, SIGTERM);

        $this->info("Terminating process with PID {$processId}");

        return 0;
    }
}
