<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskTitleChangedEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskTitleChangedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param string       $titleFrom
     * @param string       $titleTo
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        protected string $titleFrom,
        protected string $titleTo,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskTitleChanged()->value, $titleTranslationKey, $variables);
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
            'from_title' => $this->titleFrom,
            'to_title' => $this->titleTo,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskTitleChangedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskTitleChangedEmail($notifiable, $this->task, $this->titleFrom, $this->titleTo, $this->changedById))->to($notifiable->email);
    }
}
