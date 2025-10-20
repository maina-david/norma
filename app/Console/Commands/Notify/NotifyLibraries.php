<?php

namespace App\Console\Commands\Notify;

use App\Jobs\Notify\NotifyLibraries as NotifyLibrariesJob;
use Illuminate\Console\Command;

class NotifyLibraries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norma:notify-libraries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies all libraries related to the updates that have not been notified yet';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        NotifyLibrariesJob::dispatch();

        return 0;
    }
}
