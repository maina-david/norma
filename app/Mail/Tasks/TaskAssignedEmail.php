<?php

namespace App\Mail\Tasks;

use App\Models\Auth\User;
use App\Models\Tasks\Task;

class TaskAssignedEmail extends AbstractTaskMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Task $task, protected int $assignerId)
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
        /** @var User */
        $assigner = User::findOrFail($this->assignerId);

        /** @var string */
        $subject = __('notifications.task_assigned_subject', ['name' => $this->user->fname]);

        return $this->markdown('emails.tasks.task-assigned')
            ->subject($subject)
            ->with(['assigner' => $assigner->full_name]);
    }
}
