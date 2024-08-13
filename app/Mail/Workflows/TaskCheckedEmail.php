<?php

namespace App\Mail\Workflows;

use App\Models\Workflows\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskCheckedEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Task $task
     */
    public function __construct(public Task $task)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var string $subject */
        $subject = __('notifications.collaborate.task_checked');

        return $this->subject($subject)->markdown('emails.workflows.task-checked', ['task' => $this->task]);
    }
}
