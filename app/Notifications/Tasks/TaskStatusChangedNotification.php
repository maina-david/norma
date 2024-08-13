<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskStatusChangedEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskStatusChangedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param int          $statusFrom
     * @param int          $statusTo
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        protected int $statusFrom,
        protected int $statusTo,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskStatusChanged()->value, $titleTranslationKey, $variables);
        $this->task = $task;
        $this->changedById = $changedById;
        $this->statusFrom = $statusFrom;
        $this->statusTo = $statusTo;
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
            'task_status_from' => $this->statusFrom,
            'task_status_to' => $this->statusTo,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskStatusChangedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskStatusChangedEmail($notifiable, $this->task, $this->statusFrom, $this->statusTo, $this->changedById))->to($notifiable->email);
    }
}
