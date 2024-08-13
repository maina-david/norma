<?php

namespace App\Http\Controllers\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Mail\Collaborators\ProvisionalAcceptanceMail;
use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ApplicationMailController extends Controller
{
    /**
     * Resend the appropriate mail.
     *
     * @param CollaboratorApplication $application
     *
     * @return RedirectResponse
     */
    public function resend(CollaboratorApplication $application): RedirectResponse
    {
        if (ApplicationStage::one()->is($application->stage) && ApplicationStatus::approved()->is($application->application_state)) {
            Mail::to($application->email)->send(new ProvisionalAcceptanceMail($application));

            $this->notifySuccessfulUpdate();
        }

        return redirect()->route('collaborate.collaborator-applications.show', [
            'collaborator_application' => $application->id,
        ]);
    }
}
