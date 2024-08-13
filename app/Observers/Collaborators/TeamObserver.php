<?php

namespace App\Observers\Collaborators;

use App\Actions\Collaborators\Team\HandleUpdated;
use App\Models\Collaborators\Team;

class TeamObserver
{
    /**
     * Handle the Team "updated" event.
     *
     * @param \App\Models\Collaborators\Team $team
     *
     * @return void
     */
    public function updated(Team $team)
    {
        app(HandleUpdated::class)->handle($team);
    }
}
