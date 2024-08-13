<?php

namespace App\Listeners\Collaborators;

use App\Events\Collaborators\CollaboratorApplicationUpdated as CollaboratorApplicationUpdatedEvent;
use App\Mail\Collaborators\CollaboratorApplicationUpdated;
use Illuminate\Support\Facades\Mail;

class CollaboratorApplicationUpdatedSubscriber
{
    /**
     * Handle the event.
     *
     * @param CollaboratorApplicationUpdatedEvent $event
     *
     * @return void
     */
    public function handle(CollaboratorApplicationUpdatedEvent $event)
    {
        Mail::to(config('collaborate.emails.collaborator_applications'))->send(new CollaboratorApplicationUpdated($event->application));
    }
}
