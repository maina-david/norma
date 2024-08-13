<?php

namespace App\Services\Notify;

use App\Actions\Comments\Comment\GenerateCommentableLink;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Notify\Notification;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;
use Illuminate\Database\Eloquent\Collection;
use IteratorAggregate;
use Throwable;

class NotificationPresenter
{
    public function __construct(protected TaskNotificationService $taskNotificationService, protected GenerateCommentableLink $generateCommentableLink)
    {
    }

    /**
     * @param IteratorAggregate<int, Notification> $notifications
     *
     * @return IteratorAggregate<int, Notification>
     */
    public function gatherViewData(IteratorAggregate $notifications): IteratorAggregate
    {
        $userIds = $commentIds = $taskIds = [];
        foreach ($notifications as $notification) {
            $title = $notification->data['title'] ?? [];
            $body = $notification->data['body'] ?? [];

            // $notification->presentViewData = $notification->data;
            if (!empty($title['variables']['user']) && is_numeric($title['variables']['user'])) {
                $userIds[] = (int) $title['variables']['user'];
            }
            if (!empty($body['task_id'])) {
                $taskIds[] = (int) $body['task_id'];
            }
            if (!empty($body['comment_id'])) {
                $commentIds[] = (int) $body['comment_id'];
            }
            if (!empty($body['changed_by_id'])) {
                $userIds[] = (int) $body['changed_by_id'];
            }
            if (!empty($body['from_user_id'])) {
                $userIds[] = (int) $body['from_user_id'];
            }
            if (!empty($body['to_user_id'])) {
                $userIds[] = (int) $body['to_user_id'];
            }
            if (!empty($body['assigned_to'])) {
                $userIds[] = (int) $body['assigned_to'];
            }
            if (!empty($body['assigned_by'])) {
                $userIds[] = (int) $body['assigned_by'];
            }
            if (!empty($body['completed_by_id'])) {
                $userIds[] = (int) $body['completed_by_id'];
            }

            // TODO: Add reminder_id
            // if (!empty($body['reminder_id'])) {
            //    $reminderIds[] = (int) $body['reminder_id'];
            // }
        }

        /** @var Collection<int,User> */
        $users = empty($userIds) ? (new User())->newCollection() : User::whereKey($userIds)->get()->keyBy('id');
        /** @var Collection<int,Task> */
        $tasks = empty($taskIds) ? (new Task())->newCollection() : Task::whereKey($taskIds)->with(['author', 'assignee', 'watchers'])->get()->keyBy('id');
        /** @var Collection<int,Comment> */
        $comments = empty($commentIds) ? (new Comment())->newCollection() : Comment::whereKey($commentIds)->with(['commentable'])->get()->keyBy('id');
        foreach ($notifications as &$notification) {
            $title = $notification->data['title'] ?? [];
            $body = $notification->data['body'] ?? [];
            $notification->presentViewData = $notification->data;
            if (isset($title['variables']['user']) && is_numeric($title['variables']['user'])) {
                /** @var User */
                $user = $users[(int) $title['variables']['user']] ?? null;
                $notification->presentViewData['title']['variables']['user'] = $user->full_name ?? __('interface.unknown');
            }
            if (!empty($body['task_id'])) {
                $task = $tasks[$body['task_id']] ?? null;
                $notification->presentViewData['body']['task'] = $task;
            }
            if (!empty($body['comment_id'])) {
                $comment = $comments[$body['comment_id']] ?? null;
                $notification->presentViewData['body']['comment'] = $comment;
            }
            if (isset($body['changed_by_id'])) {
                /** @var User|null */
                $user = $users[$body['changed_by_id']] ?? null;
                $notification->presentViewData['body']['changed_by'] = $user->full_name ?? __('interface.unknown');
            }
            if (isset($body['from_user_id'])) {
                /** @var User|null */
                $user = $users[$body['from_user_id']] ?? null;
                $notification->presentViewData['body']['from_user'] = $user->full_name ?? __('interface.unknown');
            }
            if (isset($body['to_user_id'])) {
                /** @var User|null */
                $user = $users[$body['to_user_id']] ?? null;
                $notification->presentViewData['body']['to_user'] = $user->full_name ?? __('interface.unknown');
            }
            if (isset($body['assigned_to'])) {
                /** @var User|null */
                $user = $users[$body['assigned_to']] ?? null;
                $notification->presentViewData['body']['assigned_to'] = $user->full_name ?? __('interface.unknown');
            }
            if (isset($body['assigned_by'])) {
                /** @var User|null */
                $user = $users[$body['assigned_by']] ?? null;
                $notification->presentViewData['body']['assigned_by'] = $user->full_name ?? __('interface.unknown');
            }
            if (isset($body['completed_by_id'])) {
                /** @var User|null */
                $user = $users[$body['completed_by_id']] ?? null;
                $notification->presentViewData['body']['completed_by'] = $user->full_name ?? __('interface.unknown');
            }
            // TODO: Add reminder_id
            // if (!empty($body['reminder_id'])) {
            // }

            if (isset($body['task_status_from'])) {
                try {
                    $notification->presentViewData['body']['task_status_from'] = TaskStatus::lang()[TaskStatus::fromValue($body['task_status_from'])->value];
                } catch (Throwable $th) {
                    $notification->presentViewData['body']['task_status_from'] = __('interface.unknown');
                }
            }
            if (isset($body['task_status_to'])) {
                try {
                    $notification->presentViewData['body']['task_status_to'] = TaskStatus::lang()[TaskStatus::fromValue($body['task_status_to'])->value];
                } catch (Throwable $th) {
                    $notification->presentViewData['body']['task_status_to'] = __('interface.unknown');
                }
            }
            if (isset($body['task_priority_from'])) {
                try {
                    $notification->presentViewData['body']['task_priority_from'] = TaskPriority::lang()[TaskPriority::fromValue($body['task_priority_from'])->value];
                } catch (Throwable $th) {
                    $notification->presentViewData['body']['task_priority_from'] = __('interface.unknown');
                }
            }
            if (isset($body['task_priority_to'])) {
                try {
                    $notification->presentViewData['body']['task_priority_to'] = TaskPriority::lang()[TaskPriority::fromValue($body['task_priority_to'])->value];
                } catch (Throwable $th) {
                    $notification->presentViewData['body']['task_priority_to'] = __('interface.unknown');
                }
            }
        }

        return $notifications;
    }

    /**
     * @param array<string, mixed> $presentViewData
     *
     * @return string
     */
    public function getTitle(array $presentViewData): string
    {
        /** @var string */
        return __($presentViewData['title']['translation_key'], $presentViewData['title']['variables'] ?? []);
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function getIcon(Notification $notification): string
    {
        if ($notification->isTypeMention()) {
            return 'at';
        }
        if ($notification->isTypeTaskDue()) {
            return 'alarm-exclamation';
        }
        if ($notification->isForComment()) {
            return 'comment';
        }
        if ($notification->isForTask()) {
            return 'tasks';
        }
        if ($notification->isForReminder()) {
            return 'alarm-exclamation';
        }

        return 'bells';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function getLink(Notification $notification): string
    {
        if ($notification->isForTask()) {
            $task = $notification->presentViewData['body']['task'] ?? null;

            return is_null($task) ? '' : route('my.tasks.tasks.show', ['task' => $task->hash_id]);
        }
        if ($notification->isForComment()) {
            $comment = $notification->presentViewData['body']['comment'] ?? null;
            if (!$comment) {
                return '';
            }

            // if it's a comment reply, generate the link for the parent comment
            if ($comment->commentable instanceof Comment) {
                return $this->generateCommentableLink->handle($comment->commentable);
            }

            return $this->generateCommentableLink->handle($comment);
        }
        if ($notification->isForReminder()) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        return '';
    }
}
