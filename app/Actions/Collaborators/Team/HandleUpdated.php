<?php

namespace App\Actions\Collaborators\Team;

use App\Mail\Collaborators\TeamDetailsUpdated;
use App\Models\Collaborators\Team;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleUpdated
{
    use AsAction;

    public function handle(Team $team): void
    {
        $dirty = $team->getDirty();
        /** @var array<string> */
        $emails = config('collaborate.emails.team_details_change');
        Mail::to($emails)->send(new TeamDetailsUpdated($team->id, $dirty));
    }
}
