<?php

namespace App\Notifications\Workflows;

use App\Mail\Workflows\TaskCheckedEmail;
use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskCheckedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Task $task
     */
    public function __construct(protected Task $task)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'status' => $this->task->task_status,
            'due_on' => $this->task->due_date,
            'assigned_to' => $this->task->assignee,
            'lines' => $this->getLines(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return TaskCheckedEmail|null
     */
    public function toMail($notifiable)
    {
        if (!$notifiable->email) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $this->task->load(['assignee']);

        return (new TaskCheckedEmail($this->task))->to($notifiable->email);
    }

    /**
     * @return array<string>
     */
    private function getLines()
    {
        return [
            __('notifications.collaborate.task_checked_message'),
            '#' . $this->task->id . ' ' . ($this->task->title ?? ''),
        ];
    }
}
