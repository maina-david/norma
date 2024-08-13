<?php

namespace App\Console\Commands\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Console\Command;

class NotifyDueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libryo:notify-due-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications for tasks that are due';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Notifying due tasks');

        Task::whereNotNull('due_on')
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('due_on', '<', now('UTC')->addhours(24));
                    $q->where('due_on', '>=', now('UTC'));
                })
                    ->orWhere(function ($q) {
                        $q->where('due_on', '<=', now('UTC'));
                        $q->where('due_on', '>', now('UTC')->subDays(2));
                        $q->whereNotIn('task_status', [
                            TaskStatus::done()->value,
                            TaskStatus::paused()->value,
                        ]);
                    });
            })
            ->cursor()
            ->each(function ($task) {
                $this->info('Sending notifications for task ID ' . $task->id);

                if ($task->author && $this->is8AmTimezone($task->author)) {
                    \App\Jobs\Tasks\NotifyDueTasks::dispatch($task);
                }
            });

        $this->info('Successfully notified relevant tasks.');

        return 0;
    }

    /**
     * Checks that the time in the user's timezone is 8am.
     *
     * @param User $user
     *
     * @return bool
     */
    protected function is8AmTimezone(User $user): bool
    {
        return now($user->timezone)->is('8am');
    }
}
