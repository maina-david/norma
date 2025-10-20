<?php

namespace App\Console\Commands\Notify;

use App\Jobs\Notify\SendReminderNotifications as SendReminderJob;
use App\Models\Notify\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norma:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for reminder that are due and have not yet sent out notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Reminder::has('remindable')
            ->where('remind_on', '<=', Carbon::now('UTC')->addMinutes(5))
            ->where('remind_on', '>', Carbon::now('UTC')->subDays(2))
            ->where('reminded', false)
            ->chunk(100, function ($reminders) {
                $this->info('Syncing ' . $reminders->count() . ' reminders.');

                $reminders->each(fn ($reminder) => SendReminderJob::dispatch($reminder));
            });

        $this->info('Successfully synced all reminders.');

        return 0;
    }
}
