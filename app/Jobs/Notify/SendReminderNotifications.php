<?php

namespace App\Jobs\Notify;

use App\Enums\Tasks\TaskUser;
use App\Models\Auth\User;
use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use App\Notifications\Notify\ReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendReminderNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $translationKey = 'notify.reminder.reminder_subject';

    /**
     * Create a new job instance.
     *
     * @param Reminder $reminder
     */
    public function __construct(protected Reminder $reminder)
    {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Collection<int, User> $toNotify */
        $toNotify = collect();

        $this->reminder->load(['author', 'norma']);

        if (!$this->notifyTaskUsers($toNotify) && $this->reminder->author) {
            $toNotify->put($this->reminder->author->id, $this->reminder->author);
        }

        $this->notifyUsersInNorma($toNotify);

        $toNotify->each(function ($user) {
            if ($user->active) {
                $user->notify(new ReminderNotification($user->id, $this->reminder, $this->translationKey, [
                    'title' => $this->reminder->title,
                ]));
            }
        });

        $this->reminder->update([
            'reminded' => true,
        ]);
    }

    /**
     * Add the users attached to the task to the list of people to be notified.
     *
     * @param Collection<int, User> $toNotify
     *
     * @return bool
     */
    protected function notifyTaskUsers(Collection $toNotify): bool
    {
        $config = $this->reminder->notification_config;

        if (!$config || $this->reminder->remindable_type !== 'task') {
            return false;
        }
        // @codeCoverageIgnoreStart
        if (!$task = Task::with(['assignee', 'author', 'watchers'])->find($this->reminder->remindable_id)) {
            return true;
        }
        // @codeCoverageIgnoreEnd

        /** @var Task $task */
        if (in_array(TaskUser::assignee()->value, $config) && $task->assignee) {
            $toNotify->put($task->assignee->id, $task->assignee);
        }

        if (in_array(TaskUser::creator()->value, $config) && $task->author) {
            $toNotify->put($task->author->id, $task->author);
        }

        if (in_array(TaskUser::followers()->value, $config)) {
            foreach ($task->watchers as $watcher) {
                $toNotify->put($watcher->id, $watcher);
            }
        }

        return true;
    }

    /**
     * Add the users in the norma to the list of people to be notified.
     *
     * @param Collection<int, User> $toNotify
     *
     * @return void
     */
    protected function notifyUsersInNorma(Collection $toNotify): void
    {
        if ($this->reminder->place_id && $this->reminder->norma) {
            /** @var int $org */
            $org = $this->reminder->norma->organisation_id;
            User::normaAccess($this->reminder->norma)
                ->inOrganisation($org)
                ->chunk(100, function ($users) use ($toNotify) {
                    $users->each(function ($user) use ($toNotify) {
                        $toNotify->put($user->id, $user);
                    });
                });
        }
    }
}
