<?php

namespace App\Listeners\Collaborators;

use App\Events\Collaborators\CollaboratorApplicationCreated;
use App\Mail\Collaborators\CollaboratorApplicationReceived;
use App\Mail\Collaborators\CollaboratorApplicationSubmitted;
use Illuminate\Support\Facades\Mail;

class CollaboratorApplicationCreatedSubscriber
{
    /**
     * Handle the event.
     *
     * @param CollaboratorApplicationCreated $event
     *
     * @return void
     */
    public function handle(CollaboratorApplicationCreated $event)
    {
        Mail::to(config('collaborate.emails.collaborator_applications'))->send(new CollaboratorApplicationSubmitted($event->application));
        Mail::to($event->application->email)->send(new CollaboratorApplicationReceived($event->application));
    }
}
