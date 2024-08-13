<?php

namespace App\Jobs\Tasks;

use App\Enums\Tasks\TaskRepeatInterval;
use App\Enums\Tasks\TaskStatus;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CreateRecurringTasks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Task::whereNotNull('frequency')
            ->doesntHave('next')
            ->where(function (Builder $query) {
                collect(TaskRepeatInterval::cases())->each(function (TaskRepeatInterval $interval) use ($query) {
                    $query->orWhere(function (Builder $builder) use ($interval) {
                        $builder->where('frequency_interval', $interval->value)
                            ->whereRaw("DATE(TIMESTAMPADD({$interval->name}, frequency, tasks.created_at)) <= ?", [now()->toDateString()]);
                    });
                });
            })
            ->chunkById(200, function (Collection $tasks) {
                $tasks->each(function ($task) {
                    /** @var Task $task */
                    if ($task->created_at) {
                        $this->evaluateTask($task);
                    }
                });
            });
    }

    /**
     * Evaluate the task if it is ready to be re-created and create it.
     *
     * @param \App\Models\Tasks\Task $task
     *
     * @return void
     */
    protected function evaluateTask(Task $task): void
    {
        $dateCreated = Carbon::parse($task->created_at)->startOfDay();
        $toCreateOn = Carbon::parse($dateCreated)->add($task->frequency_interval->name, $task->frequency ?? 1);

        if (now()->startOfDay()->gte($toCreateOn)) {
            $toCreate = $task->replicate(['archived', 'completed_at', 'created_at', 'updated_at']);
            $toCreate->task_status = TaskStatus::notStarted()->value;
            $toCreate->previous_task_id = $task->id;
            $toCreate->setCreatedAt($toCreateOn);
            $toCreate->setUpdatedAt($toCreateOn);
            $toCreate->due_on = $task->due_on
                ? $toCreateOn->clone()->addDays((int) $task->due_on->startOfDay()->diffInDays($dateCreated, true)) : null;
            $toCreate->save();
        }
    }
}
