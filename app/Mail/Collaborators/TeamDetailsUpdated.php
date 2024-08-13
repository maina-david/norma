<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamDetailsUpdated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param int           $teamId
     * @param array<string> $dirty
     */
    public function __construct(protected int $teamId, protected array $dirty)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var Team */
        $team = Team::findOrFail($this->teamId);
        /** @var string */
        $subject = __('collaborators.team.team_details_updated_subject');

        return $this->subject($subject)
            ->markdown('emails.collaborators.team-details-updated', [
                'team' => $team,
                'dirty' => $this->dirty,
            ]);
    }
}
