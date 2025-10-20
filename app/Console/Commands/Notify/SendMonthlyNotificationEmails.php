<?php

namespace App\Console\Commands\Notify;

use App\Jobs\Notify\SendMonthlyNotificationEmails as NotificationJob;
use Illuminate\Console\Command;

class SendMonthlyNotificationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norma:monthly-notification-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a monthly summary of notifications to user email addresses.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        NotificationJob::dispatch();

        return 0;
    }
}
