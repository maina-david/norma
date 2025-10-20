<?php

namespace App\Console\Commands\Notify;

use App\Jobs\Notify\SendDailyNotificationEmails as NotificationJob;
use Illuminate\Console\Command;

class SendDailyNotificationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norma:daily-notification-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends all unsent notifications to user email addresses.';

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
