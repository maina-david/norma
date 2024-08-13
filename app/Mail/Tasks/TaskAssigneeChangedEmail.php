<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskAssigneeChangedEmail extends AbstractTaskMail
{
    /**
     * @param User     $user
     * @param Task     $task
     * @param int|null $fromUserId
     * @param int|null $toUserId
     * @param int      $changedById
     */
    public function __construct(
        User $user,
        Task $task,
        protected ?int $fromUserId,
        protected ?int $toUserId,
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
        /** @var User|null */
        $fromUser = User::find($this->fromUserId);
        /** @var User|null */
        $toUser = User::find($this->toUserId);

        $relationship = app(TaskNotificationService::class)->getUserTaskRelationship($this->task, $this->user) . '_';

        if ($this->fromUserId === $this->user->id) {
            $relationship = 'previous_';
        }

        /** @var string */
        $subject = __('notifications.task_assignee_changed_' . $relationship . 'subject');

        /** @var string */
        $unassigned = __('tasks.unassigned');

        return $this->markdown('emails.tasks.task-assignee-changed')
            ->subject($subject)
            ->with([
                'changedBy' => $this->getChangedBy(),
                'fromUser' => $fromUser?->full_name ?? $unassigned,
                'toUser' => $toUser?->full_name ?? $unassigned,
            ]);
    }
}
