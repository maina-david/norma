<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskTitleChangedEmail extends AbstractTaskMail
{
    /**
     * @param User   $user
     * @param Task   $task
     * @param string $fromTitle
     * @param string $toTitle
     * @param int    $changedById
     */
    public function __construct(
        User $user,
        Task $task,
        protected string $fromTitle,
        protected string $toTitle,
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
        $sub = __('notifications.task_title_changed_' . $relationship . 'subject', ['title' => $this->toTitle]);

        return $this->markdown('emails.tasks.task-title-changed')
            ->subject($sub)
            ->with([
                'changedBy' => $this->getChangedBy(),
                'fromTitle' => $this->fromTitle,
                'toTitle' => $this->toTitle,
            ]);
    }
}
