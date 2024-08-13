<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskCompleteEmail extends AbstractTaskMail
{
    /**
     * @param User $user
     * @param Task $task
     * @param int  $changedById
     */
    public function __construct(
        User $user,
        Task $task,
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
        $relationship = app(TaskNotificationService::class)->getUserTaskRelationship($this->task, $this->user);

        /** @var string */
        $sub = __('notifications.task_complete_' . $relationship . '_subject');

        return $this->markdown('emails.tasks.task-complete')
            ->subject($sub)
            ->with([
                'introLine' => trans('tasks.task_completed_' . $relationship),
                'completedBy' => $this->getChangedBy(),
            ]);
    }
}
