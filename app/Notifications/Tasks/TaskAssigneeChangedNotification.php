<?php

namespace App\Notifications\Tasks;

use App\Enums\Notifications\NotificationType;
use App\Mail\Tasks\TaskAssigneeChangedEmail;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Bus\Queueable;

class TaskAssigneeChangedNotification extends AbstractTaskNotification
{
    use Queueable;

    /**
     * @param Task         $task
     * @param int          $changedById
     * @param int|null     $userFrom
     * @param int|null     $userTo
     * @param string       $titleTranslationKey
     * @param array<mixed> $variables
     */
    public function __construct(
        protected Task $task,
        protected int $changedById,
        protected ?int $userFrom,
        protected ?int $userTo,
        string $titleTranslationKey,
        array $variables = []
    ) {
        parent::__construct(NotificationType::taskAssigneeChanged()->value, $titleTranslationKey, $variables);
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
            'from_user_id' => $this->userFrom,
            'to_user_id' => $this->userTo,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskAssigneeChangedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return (new TaskAssigneeChangedEmail($notifiable, $this->task, $this->userFrom, $this->userTo, $this->changedById))->to($notifiable->email);
    }
}
