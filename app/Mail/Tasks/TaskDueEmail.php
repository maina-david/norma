<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskNotificationService;

class TaskDueEmail extends AbstractTaskMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Task $task)
    {
        parent::__construct($user, $task);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notificationService = app(TaskNotificationService::class);
        /** @var User|null */
        $assignee = User::find($this->task->assigned_to_id);
        $assignedTo = $assignee?->full_name;

        $subject = $notificationService->getTaskDueSubjectTransKey($this->task, $this->user);

        $introLine = $notificationService->getTaskDueIntroLine($this->task, $this->user);

        if ($this->task->isAssignee($this->user)) {
            $assignedTo = null;
        }

        /** @var string */
        $sub = __($subject);

        return $this->markdown('emails.tasks.task-due')
            ->subject($sub)
            ->with([
                'assignedTo' => $assignedTo,
                'introLine' => $introLine,
            ]);
    }
}
