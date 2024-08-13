<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskDueEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskDueNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $currentStatus
     * @param string       $dueDate
     * @param int|null     $assignee
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $currentStatus,
        protected string $dueDate,
        protected ?int $assignee,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskDue()->value, $titleTranslationKey, $variables);
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
            'status' => $this->currentStatus,
            'due_on' => $this->dueDate,
            'assigned_to' => $this->assignee,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskDueEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskDueEmail($notifiable, $this->task))->to($notifiable->email);
    }
}
