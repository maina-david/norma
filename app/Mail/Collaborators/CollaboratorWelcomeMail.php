<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\Collaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollaboratorWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected Collaborator $collaborator)
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
        /** @var string $subject */
        $subject = __('collaborators.collaborator_application.welcome_to_collaborate');

        return $this->markdown('emails.collaborators.collaborator-welcome-mail')
            ->with(['collaborator' => $this->collaborator])
            ->subject($subject);
    }
}
