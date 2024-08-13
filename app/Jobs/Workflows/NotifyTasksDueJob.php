<?php

namespace App\Jobs\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use App\Notifications\Workflows\TaskDueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyTasksDueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $allowed = [
            TaskStatus::inReview()->value,
            TaskStatus::done()->value,
            TaskStatus::archive()->value,
        ];

        Task::has('assignee')
            ->with(['assignee'])
            ->whereNotIn('task_status', $allowed)
            ->whereDate('due_date', '<=', now())
            ->whereDate('due_date', '>=', now()->subDay())
            ->whereNotNull('user_id')
            ->where(function ($builder) {
                $builder->whereNull('notified_user_id')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('notified_user_id')->whereColumn('notified_user_id', '!=', 'user_id');
                    });
            })
            ->chunk(100, function ($tasks) {
                $tasks->each(function ($task) {
                    $task->assignee?->notify(new TaskDueNotification($task));
                    $task->notified_user_id = $task->assignee->id ?? null;
                    $task->save();
                });
            });
    }
}
