<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\TaskAssigneeChanged;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use App\Notifications\Workflows\TaskAssignedNotification;
use App\Traits\Workflows\CreatesTransitions;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssigneeChangedSubscriber implements ShouldQueue
{
    use CreatesTransitions;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'notifications';

    /**
     * Listen to the assignee changed event.
     *
     * @param TaskAssigneeChanged $event
     *
     * @return void
     */
    public function handle(TaskAssigneeChanged $event): void
    {
        if (!$event->task->wasRecentlyCreated) {
            $this->createTransition($event->task, 'user_id');
        }

        $this->removeTaskApplications($event->task);
        $this->notifyAssignee($event->task);
    }

    /**
     * Remove all task assignments for the task if it is assigned.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function removeTaskApplications(Task $task): void
    {
        if ($task->user_id) {
            TaskApplication::where('task_id', $task->id)->delete();
        }
    }

    /**
     * Notify the assignee of the assignment.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function notifyAssignee(Task $task): void
    {
        $task->load('assignee');
        $task->assignee?->notify(new TaskAssignedNotification($task));
    }
}
