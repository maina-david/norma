<?php

namespace App\Listeners\Collaborators;

use App\Enums\Auth\UserType;
use App\Enums\Collaborators\ApplicationStage;
use App\Events\Collaborators\CollaboratorApplicationApproved;
use App\Mail\Collaborators\CollaboratorWelcomeMail;
use App\Mail\Collaborators\ProvisionalAcceptanceMail;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CollaboratorApplicationApprovedSubscriber
{
    /**
     * Handle the event.
     *
     * @param CollaboratorApplicationApproved $event
     *
     * @return void
     */
    public function handle(CollaboratorApplicationApproved $event)
    {
        if (ApplicationStage::one()->is($event->application->stage)) {
            Mail::to($event->application->email)->send(new ProvisionalAcceptanceMail($event->application));

            return;
        }

        if (ApplicationStage::provisional()->is($event->application->stage)) {
            $this->createCollaborator($event->application);
        }
    }

    /**
     * Create a collaborator.
     *
     * @param CollaboratorApplication $application
     *
     * @return void
     */
    protected function createCollaborator(CollaboratorApplication $application): void
    {
        $application->load(['profile']);

        DB::transaction(function () use ($application) {
            /** @var Collaborator $collaborator */
            $collaborator = Collaborator::create([
                'fname' => $application->fname,
                'sname' => $application->lname,
                'email' => $application->email,
                'user_type' => UserType::collaborate()->value,
            ]);

            /** @var Profile $profile */
            $profile = $application->profile;
            $profile->user_id = $collaborator->id;

            /** @var Team $team */
            $team = Team::create(['title' => $collaborator->full_name]);
            $profile->team_id = $team->id;
            $profile->is_team_admin = true;

            $profile->save();

            Mail::to($collaborator->email)->send(new CollaboratorWelcomeMail($collaborator));
        });
    }
}
