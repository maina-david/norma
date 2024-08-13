<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ProvisionalAcceptanceMail extends Mailable implements ShouldQueue
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
        $this->queue = 'notifications';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = URL::temporarySignedRoute(
            'collaborate.collaborator-application.stage-two.create',
            now()->addDays(14), ['application' => $this->application->id]
        );

        /** @var string $subject */
        $subject = __('collaborators.collaborator_application.provisional_email_subject');

        return $this->markdown('emails.collaborators.provisional-acceptance-mail')
            ->with(['link' => $link, 'application' => $this->application])
            ->subject($subject);
    }
}
