<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollaboratorApplicationUpdated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected CollaboratorApplication $application)
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
        $subject = __('collaborators.collaborator_application.application_updated');

        return $this->markdown('emails.collaborators.collaborator-application-updated')
            ->with(['application' => $this->application])
            ->subject($subject);
    }
}
