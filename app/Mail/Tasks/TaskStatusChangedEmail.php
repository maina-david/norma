<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskStatusChangedEmail extends AbstractTaskMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        Task $task,
        protected int $fromStatus,
        protected int $toStatus,
        protected int $changedById
    ) {
        parent::__construct($user, $task);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $relationship = app(TaskNotificationService::class)->getUserTaskRelationship($this->task, $this->user) . '_';

        /** @var string */
        $sub = __('notifications.task_status_changed_' . $relationship . 'subject', ['status' => __('tasks.task.status.' . $this->toStatus)]);

        return $this->markdown('emails.tasks.task-status-changed')
            ->subject($sub)
            ->with([
                'changedBy' => $this->getChangedBy(),
                'fromStatus' => $this->fromStatus,
                'toStatus' => $this->toStatus,
            ]);
    }
}
