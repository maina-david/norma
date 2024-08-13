<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskCompleteEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskCompletedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskCompleted()->value, $titleTranslationKey, $variables);
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
            'completed_by_id' => $this->changedById,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskCompleteEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskCompleteEmail($notifiable, $this->task, $this->changedById))->to($notifiable->email);
    }
}
