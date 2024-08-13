<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskPriorityChangedEmail extends AbstractTaskMail
{
    /**
     * @param User     $user
     * @param Task     $task
     * @param int|null $fromPriority
     * @param int|null $toPriority
     * @param int      $changedById
     */
    public function __construct(
        User $user,
        Task $task,
        protected ?int $fromPriority,
        protected ?int $toPriority,
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
        $sub = __('notifications.task_priority_changed_' . $relationship . 'subject', ['priority' => __('tasks.task.priority.' . $this->toPriority)]);
        /** @var string */
        $noPriority = __('tasks.no_priority_set');

        return $this->markdown('emails.tasks.task-priority-changed')
            ->subject($sub)
            ->with([
                'changedBy' => $this->getChangedBy(),
                'fromPriority' => $this->fromPriority ?? $noPriority,
                'toPriority' => $this->toPriority ?? $noPriority,
            ]);
    }
}
