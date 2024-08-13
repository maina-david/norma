<?php

namespace App\Http\Controllers\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Enums\Collaborators\DocumentType;
use App\Events\Collaborators\CollaboratorApplicationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Collaborators\StageTwoApplicationRequest;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Stores\Collaborators\ProfileDocumentStore;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class StageTwoController extends Controller
{
    /**
     * Get the stage two form.
     *
     * @param Request                 $request
     * @param CollaboratorApplication $application
     *
     * @return View
     */
    public function create(Request $request, CollaboratorApplication $application): View
    {
        if (!$request->hasValidSignature() || !ApplicationStage::one()->is($application->stage) || !ApplicationStatus::approved()->is($application->application_state)) {
            abort(404);
        }

        $application->load(['profile']);

        /** @var View */
        return view('pages.collaborators.collaborate.applications.stage-two', ['application' => $application]);
    }

    /**
     * Update the application.
     *
     * @param StageTwoApplicationRequest $request
     * @param CollaboratorApplication    $application
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function update(StageTwoApplicationRequest $request, CollaboratorApplication $application): RedirectResponse
    {
        $application->load(['profile']);

        /** @var Profile $profile */
        $profile = $application->profile;

        $profile->address_line_1 = $request->get('address_line_1');
        $profile->postal_address = $request->get('postal_address');
        $profile->process_personal_data = $request->get('process_personal_data');

        /** @var UploadedFile $file */
        $file = $request->file('identity');

        $store = app(ProfileDocumentStore::class);
        $store->put(DocumentType::IDENTIFICATION, $profile, $file);

        if ($request->hasFile('visa')) {
            /** @var UploadedFile $file */
            $file = $request->file('visa');

            $store->put(DocumentType::VISA, $profile, $file);
        }

        $profile->save();

        $application->update([
            'stage' => ApplicationStage::two()->value,
            'application_state' => ApplicationStatus::pending()->value,
        ]);

        CollaboratorApplicationUpdated::dispatch($application);

        return redirect()->route('collaborate.collaborator-application.stage-two.thanks', ['application' => $application->id]);
    }

    /**
     * @param CollaboratorApplication $application
     *
     * @return View
     */
    public function thanks(CollaboratorApplication $application): View
    {
        /** @var View */
        return view('pages.collaborators.collaborate.applications.stage-two-thanks', ['application' => $application]);
    }
}
