<?php

namespace App\Events\Collaborators;

use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollaboratorApplicationDeclined
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param CollaboratorApplication $application
     */
    public function __construct(public CollaboratorApplication $application)
    {
    }
}
