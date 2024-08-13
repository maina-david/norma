<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskDueDateChangedEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;

class TaskDueDateChangedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param string|null  $dateFrom
     * @param string|null  $dateTo
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        protected ?string $dateFrom,
        protected ?string $dateTo,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskDueDateChanged()->value, $titleTranslationKey, $variables);
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
            'from_due_on' => $this->dateFrom,
            'to_due_on' => $this->dateTo,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskDueDateChangedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskDueDateChangedEmail(
            $notifiable,
            $this->task,
            $this->dateFrom ? Carbon::parse($this->dateFrom) : null,
            $this->dateTo ? Carbon::parse($this->dateTo) : null,
            $this->changedById
        ))->to($notifiable->email);
    }
}
