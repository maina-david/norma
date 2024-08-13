<?php

namespace App\Jobs\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Notifications\Tasks\TaskDueNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyDueTasks implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 43200; // 12 hours

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Task $task)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->task->getNotifiables()->each(function ($user) {
            /** @var User $user */
            $key = $this->task->getNotificationSubjectTranslationKey($user);

            $user->notify(
                new TaskDueNotification(
                    $this->task,
                    $this->task->task_status,
                    Carbon::parse($this->task->due_on)->toDateString(),
                    $this->task->assigned_to_id,
                    $key
                )
            );
        });
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return static::class . '_' . $this->task->id;
    }
}
