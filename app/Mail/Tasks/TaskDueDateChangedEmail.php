<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;
use DateTimeInterface;

class TaskDueDateChangedEmail extends AbstractTaskMail
{
    /**
     * @param User                   $user
     * @param Task                   $task
     * @param DateTimeInterface|null $fromDueOn
     * @param DateTimeInterface|null $toDueOn
     * @param int                    $changedById
     */
    public function __construct(
        User $user,
        Task $task,
        protected ?DateTimeInterface $fromDueOn,
        protected ?DateTimeInterface $toDueOn,
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
        $sub = __('notifications.task_due_date_changed_' . $relationship . 'subject');
        /** @var string */
        $noDueDate = __('tasks.no_due_date');

        return $this->markdown('emails.tasks.task-due-date-changed')
            ->subject($sub)
            ->with([
                'changedBy' => $this->getChangedBy(),
                'fromDate' => !is_null($this->fromDueOn)
                    ? $this->formatDate($this->fromDueOn)
                    : $noDueDate,

                'toDate' => !is_null($this->toDueOn)
                    ? $this->formatDate($this->toDueOn)
                    : $noDueDate,
            ]);
    }
}
