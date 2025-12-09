<?php

namespace App\Observers\Tasks;

use App\Actions\Tasks\Task\HandleTaskCreated;
use App\Actions\Tasks\Task\HandleTaskCreating;
use App\Actions\Tasks\Task\HandleTaskSaving;
use App\Actions\Tasks\Task\HandleTaskUpdated;
use App\Models\Tasks\Task;

class TaskObserver
{
    /**
     * Listen to the Task saving event.
     *
     * @param Task $task
     */
    public function saving(Task $task): void
    {
        HandleTaskSaving::run($task);
    }

    public function creating(Task $task): void
    {
        HandleTaskCreating::run($task);
    }

    public function updated(Task $task): void
    {
        HandleTaskUpdated::run($task);
    }

    /**
     * Listen to the Task updating event.
     *
     * @param Task $task
     */
    // public function updating(Task $task)
    // {
    //     if ($user = auth()->user()) {
    //         if ($task->isDirty('due_on')) {
    //             $timezone = $user->timezone ?? 'UTC';

    //             $task->due_on = Carbon::parse($task->due_on, $timezone)
    //                 ->setTimezone('UTC');
    //         }

    //         if ($task->isDirty('task_status')) {
    //             $task->completed_at = $task->task_status === Task::STATUS_DONE ? now() : null;
    //         }
    //     }
    // }

    /**
     * Listen to the Task updated event.
     *
     * @param Task $task
     */
    // public function updated(Task $task)
    // {
    //     if ($user = auth()->user()) {
    //         if ($task->isDirty('title')) {
    //             $this->handleTitleChange($task, $user);
    //         }

    //         if ($task->isDirty('task_status')) {
    //             $this->handleTaskStatusChange($task, $user);
    //         }

    //         if ($task->isDirty('priority')) {
    //             $this->handleTaskPriorityChange($task, $user);
    //         }

    //         if ($task->isDirty('due_on')) {
    //             $this->handleDueOnChange($task, $user);
    //         }

    //         if ($task->isDirty('assigned_to_id')) {
    //             $this->handleAssigneeChange($task, $user);
    //         }
    //     }
    // }

    /**
     * Listen to the Task created event.
     *
     * @param Task $task
     */
    public function created(Task $task): void
    {
        HandleTaskCreated::run($task);
    }

    /**
     * Dispatches the events and notifications for a status change.
     *
     * @param Task $task
     * @param User $user
     */
    // protected function handleTaskStatusChange(Task $task, User $user)
    // {
    //     $statusFrom = $task->getOriginal('task_status');
    //     $statusTo = $task->task_status;

    //     event(new StatusChanged($user, $task, $task->norma, cast($statusFrom, 'int'), cast($statusTo, 'int')));

    //     if ($statusTo === Task::STATUS_DONE) {
    //         HandleTaskCompleteNotification::dispatch($task, $user);
    //     } else {
    //         HandleTaskStatusChangeNotification::dispatch($task, $user, cast($statusFrom, 'int'), cast($statusTo, 'int'));
    //     }
    // }

    /**
     * Dispatches the events and notifications for a priority change.
     *
     * @param Task $task
     * @param User $user
     */
    // protected function handleTaskPriorityChange(Task $task, User $user)
    // {
    //     $from = $task->getOriginal('priority');
    //     $to = $task->priority;

    //     event(new PriorityChanged($user, $task, $task->norma, $from, $to));
    //     HandleTaskPriorityChangeNotification::dispatch($task, $user, cast($from, 'int'), cast($to, 'int'));
    // }

    /**
     * Dispatches the events and notifications for a due date change.
     *
     * @param Task $task
     * @param User $user
     */
    // protected function handleDueOnChange(Task $task, User $user)
    // {
    //     $dueFrom = Carbon::parse($task->getOriginal('due_on'));
    //     $dueTo = Carbon::parse($task->due_on);

    //     if (!$dueFrom->equalTo($dueTo)) {
    //         event(new DueDateChanged($user, $task, $task->norma, $dueFrom, $dueTo));
    //         HandleTaskDueDateChangeNotification::dispatch($task, $user, $dueFrom, $dueTo);
    //     }
    // }

    /**
     * Dispatches the events and notifications for a assignee change.
     *
     * @param Task $task
     * @param User $user
     */
    // protected function handleAssigneeChange(Task $task, User $user)
    // {
    //     $assignedFrom = $task->getOriginal('assigned_to_id');
    //     $assignedTo = $task->assigned_to_id;

    //     event(new AssigneeChanged($user, $task, $task->norma, $assignedFrom, $assignedTo));
    //     HandleTaskAssigneeChangeNotification::dispatch($task, $user, cast($assignedFrom, 'int'), cast($assignedTo, 'int'));
    // }

    /**
     * Dispatches the events and notifications for a title change.
     *
     * @param Task $task
     * @param User $user
     */
    // protected function handleTitleChange(Task $task, User $user)
    // {
    //     $titleFrom = $task->getOriginal('title');
    //     $titleTo = $task->title;

    //     event(new TitleChanged($user, $task, $task->norma, $titleFrom, $titleTo));
    //     HandleTaskTitleChangeNotification::dispatch($task, $user, $titleFrom, $titleTo);
    // }
}
