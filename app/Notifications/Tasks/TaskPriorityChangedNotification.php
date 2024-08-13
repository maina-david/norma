<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskPriorityChangedEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskPriorityChangedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param int|null     $priorityFrom
     * @param int|null     $priorityTo
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        protected ?int $priorityFrom,
        protected ?int $priorityTo,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskPriorityChanged()->value, $titleTranslationKey, $variables);
    }

    /**
     * Get the database format of the notification to be persisted.
     *
     * @param User $notifiable
     *
     * @return array<string, mixed>
     */
    public function databaseFormat($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'changed_by_id' => $this->changedById,
            'task_priority_from' => $this->priorityFrom,
            'task_priority_to' => $this->priorityTo,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskPriorityChangedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskPriorityChangedEmail($notifiable, $this->task, $this->priorityFrom, $this->priorityTo, $this->changedById))->to($notifiable->email);
    }
}
