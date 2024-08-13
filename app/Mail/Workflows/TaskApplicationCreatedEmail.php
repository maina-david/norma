<?php

namespace App\Mail\Workflows;

use App\Models\Workflows\TaskApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskApplicationCreatedEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param TaskApplication $application
     */
    public function __construct(public TaskApplication $application)
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
        $subject = __('notifications.collaborate.new_task_application');

        $this->application->load(['task']);

        return $this->subject($subject)->markdown('emails.workflows.task-application-created', ['application' => $this->application]);
    }
}
