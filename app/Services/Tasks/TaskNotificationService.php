<?php

namespace App\Services\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;

class TaskNotificationService
{
    /**
     * Gets the translation key for a task notification subject for a user.
     *
     * @param Task $task
     * @param User $user
     *
     * @return string
     */
    public function getTaskDueSubjectTransKey(Task $task, User $user): string
    {
        $dueOrOverdue = $task->isOverdue() ? 'overdue' : 'due';

        $subject = 'notifications.task_' . $dueOrOverdue . '_subject';

        if ($task->isWatcher($user)) {
            $subject = 'notifications.task_' . $dueOrOverdue . '_watching_subject';
        }

        if ($task->isAuthor($user)) {
            $subject = 'notifications.task_' . $dueOrOverdue . '_assigned_subject';
        }

        if ($task->isAssignee($user)) {
            if (TaskStatus::done()->is($task->task_status)) {
                $subject = 'notifications.task_' . $dueOrOverdue . '_complete_assignee_subject';
            } else {
                $subject = 'notifications.task_' . $dueOrOverdue . '_incomplete_assignee_subject';
            }
        }

        return $subject;
    }

    /**
     * Gets the intro line for a task due email.
     *
     * @param Task $task
     * @param User $user
     *
     * @return string
     */
    public function getTaskDueIntroLine(Task $task, User $user): string
    {
        $status = __('tasks.task.status.' . $task->task_status);
        $dueOrOverdue = $task->isOverdue() ? 'overdue' : 'due';

        $introLine = __('tasks.task_' . $dueOrOverdue, ['status' => $status]);

        if ($task->isWatcher($user)) {
            $introLine = __('tasks.task_watching_' . $dueOrOverdue, ['status' => $status]);
        }

        if ($task->isAuthor($user)) {
            $introLine = __('tasks.task_assigned_' . $dueOrOverdue, ['status' => $status]);
        }

        if ($task->isAssignee($user)) {
            $introLine = __('tasks.task_assignee_' . $dueOrOverdue, ['status' => $status]);
        }

        /** @var string */
        return $introLine;
    }

    /**
     * Gets the relationship between the user and the task as a string.
     *
     * @param Task $task
     * @param User $user
     *
     * @return string
     */
    public function getUserTaskRelationship(Task $task, User $user): string
    {
        $relationship = '';

        if ($task->isWatcher($user)) {
            $relationship = 'watcher';
        }

        if ($task->isAuthor($user)) {
            $relationship = 'author';
        }

        if ($task->isAssignee($user)) {
            $relationship = 'assignee';
        }

        return $relationship;
    }

    /**
     * @param TaskActivity $activity
     * @param string       $relationship
     *
     * @return string
     */
    public function getUserSettingForActivity(TaskActivity $activity, string $relationship): string
    {
        $mappings = config('libryo.model_settings.' . User::class . '.notification_activity_mappings');

        return isset($mappings[$activity->activity_type]) ? $mappings[$activity->activity_type] . $relationship : '';
    }
}
