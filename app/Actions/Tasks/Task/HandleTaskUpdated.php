<?php

namespace App\Actions\Tasks\Task;

use App\Enums\Tasks\TaskActivityType;
use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Notifications\Tasks\TaskAssigneeChangedNotification;
use App\Notifications\Tasks\TaskCompletedNotification;
use App\Notifications\Tasks\TaskDueDateChangedNotification;
use App\Notifications\Tasks\TaskPriorityChangedNotification;
use App\Notifications\Tasks\TaskStatusChangedNotification;
use App\Notifications\Tasks\TaskTitleChangedNotification;
use App\Stores\Tasks\TaskActivityStore;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use RRule\RRule;

class HandleTaskUpdated
{
    use AsAction;

    /**
     * @param TaskActivityStore $taskActivityStore
     */
    public function __construct(protected TaskActivityStore $taskActivityStore)
    {
    }

    /**
     * @param Task $task
     *
     * @return void
     */
    public function handle(Task $task): void
    {
        $this->handleDirty($task);
    }

    /**
     * @param Task $task
     *
     * @return void
     */
    public function handleDirty(Task $task): void
    {
        /** @var User|null */
        $user = Auth::user();

        if (!is_null($user)) {
            if ($task->isDirty('title')) {
                $this->handleTitleChange($task, $user);
            }

            if ($task->isDirty('task_status')) {
                $this->handleTaskStatusChange($task, $user);
            }

            if ($task->isDirty('priority')) {
                $this->handleTaskPriorityChange($task, $user);
            }

            if ($task->isDirty('due_on')) {
                $this->handleDueOnChange($task, $user);
            }

            if ($task->isDirty('assigned_to_id')) {
                $this->handleAssigneeChange($task, $user);
            }
        }
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return void
     */
    public function handleTitleChange(Task $task, User $user): void
    {
        $details = [
            'from_title' => $task->getOriginal('title'),
            'to_title' => $task->title,
        ];
        $taskActivity = $this->taskActivityStore->create(
            TaskActivityType::titleChanged(),
            $task->id,
            $user->id,
            $task->place_id,
            $details
        );

        $users = User::shouldBeNotifiedOfTaskActivity($taskActivity)->get();

        Notification::send($users, new TaskTitleChangedNotification(
            $task,
            $user->id,
            $details['from_title'],
            $details['to_title'],
            'notifications.task_title_changed_subject'
        ));
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @throws Exception
     *
     * @return void
     */
    public function handleTaskStatusChange(Task $task, User $user): void
    {
        $details = [
            'from_status' => (int) $task->getOriginal('task_status'),
            'to_status' => (int) $task->task_status,
        ];
        $taskActivity = $this->taskActivityStore->create(
            TaskActivityType::statusChange(),
            $task->id,
            $user->id,
            $task->place_id,
            $details
        );

        $statusTo = TaskStatus::fromValue((int) $task->task_status);
        $users = User::shouldBeNotifiedOfTaskActivity($taskActivity)->get();

        if ($statusTo->is(TaskStatus::done())) {
            Notification::send($users, new TaskCompletedNotification(
                $task,
                $user->id,
                'notifications.task_complete_subject'
            ));

            if (!empty($task->rrule) && $task->next()->doesntExist()) {
                $this->handleTaskRecurrence($task);
            }
        } else {
            Notification::send($users, new TaskStatusChangedNotification(
                $task,
                $user->id,
                $details['from_status'],
                $details['to_status'],
                'notifications.task_complete_subject'
            ));
        }
    }

    /**
     * @param Task $task
     *
     * @throws Exception
     *
     * @return void
     */
    public function handleTaskRecurrence(Task $task): void
    {
        $startDate = Carbon::parse($task->recurrence_start_date)->startOfDay();
        if ($task->previous()->doesntExist()) {
            if (today() > $startDate) {
                return;
            }
        }
        $today = now()->startOfDay();

        $rrule = new RRule($task->rrule);

        if ($rrule->isFinite()) {
            $occurrencesCount = $rrule->count();

            if ($occurrencesCount && ($task->recurrence_count >= $occurrencesCount)) {
                return;
            }
        }

        $nextOccurrence = $rrule->getOccurrencesAfter($today, true, 1);

        if (!empty($nextOccurrence)) {
            $nextOccurrenceStart = Carbon::instance($nextOccurrence[0])->setTime(23, 59, 59);
            $replicatedTask = new Task($task->only([
                'title',
                'description',
                'taskable_type',
                'taskable_id',
                'source_task_id',
                'task_project_id',
                'place_id',
                'org_identifier',
                'action_area_id',
                'author_id',
                'assigned_to_id',
                'recurrence_start_date',
                'rrule',
                'priority',
                'impact',
                'reference_content_extract_id',
            ]));

            $replicatedTask->recurrence_count = $task->recurrence_count + 1;
            $replicatedTask->series_task_id = $task->series_task_id ?: $task->id;
            $replicatedTask->task_status = TaskStatus::notStarted()->value;
            $replicatedTask->previous_task_id = $task->id;
            $replicatedTask->due_on = $nextOccurrenceStart;
            $replicatedTask->save();
        }
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return void
     */
    public function handleTaskPriorityChange(Task $task, User $user): void
    {
        $details = [
            'from_priority' => $task->getOriginal('priority'),
            'to_priority' => $task->priority,
        ];
        $taskActivity = $this->taskActivityStore->create(
            TaskActivityType::priorityChanged(),
            $task->id,
            $user->id,
            $task->place_id,
            $details
        );

        $users = User::shouldBeNotifiedOfTaskActivity($taskActivity)->get();
        Notification::send($users, new TaskPriorityChangedNotification(
            $task,
            $user->id,
            $details['from_priority'],
            $details['to_priority'],
            'notifications.task_priority_changed_subject'
        ));
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return void
     */
    public function handleDueOnChange(Task $task, User $user): void
    {
        $details = [
            'from_due_on' => $task->getOriginal('due_on') ? (string) $task->getOriginal('due_on') : null,
            'to_due_on' => $task->due_on ? (string) $task->due_on : null,
        ];
        $taskActivity = $this->taskActivityStore->create(
            TaskActivityType::dueDateChanged(),
            $task->id,
            $user->id,
            $task->place_id,
            $details
        );

        $users = User::shouldBeNotifiedOfTaskActivity($taskActivity)->get();
        Notification::send($users, new TaskDueDateChangedNotification(
            $task,
            $user->id,
            $details['from_due_on'],
            $details['to_due_on'],
            'notifications.task_due_date_changed_subject'
        ));
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return void
     */
    public function handleAssigneeChange(Task $task, User $user): void
    {
        $details = [
            'from_user_id' => $task->getOriginal('assigned_to_id'),
            'to_user_id' => $task->assigned_to_id,
        ];
        $taskActivity = $this->taskActivityStore->create(
            TaskActivityType::assigneeChange(),
            $task->id,
            $user->id,
            $task->place_id,
            $details
        );

        $users = User::shouldBeNotifiedOfTaskActivity($taskActivity)->get();
        Notification::send($users, new TaskAssigneeChangedNotification(
            $task,
            $user->id,
            $details['from_user_id'],
            $details['to_user_id'],
            'notifications.task_assignee_changed_subject'
        ));
    }
}
