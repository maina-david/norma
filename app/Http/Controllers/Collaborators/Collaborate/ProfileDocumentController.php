<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Enums\Collaborators\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Collaborators\CollaboratorDocumentRequest;
use App\Http\Requests\Collaborators\CollaboratorDocumentValidationRequest;
use App\Mail\Collaborators\ApplicationNextStepsMail;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use App\Stores\Collaborators\ProfileDocumentStore;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class ProfileDocumentController extends Controller
{
    /**
     * Preview the file.
     *
     * @param ProfileDocumentStore $store
     * @param Request              $request
     * @param int                  $document
     *
     * @return Response
     */
    public function show(ProfileDocumentStore $store, Request $request, int $document): Response
    {
        $document = $store->get($document);

        abort_unless((bool) $document, 404);

        /** @var ProfileDocument $document */

        /** @var Route $route */
        $route = $request->route();

        /** @var string $name */
        $name = $route->getName();

        $method = str_contains($name, 'stream') ? 'stream' : 'download';

        abort_unless((bool) $response = $document->attachment->{$method}(), 404);

        return $response;
    }

    /**
     * Update the document.
     *
     * @param ProfileDocumentStore        $store
     * @param CollaboratorDocumentRequest $request
     * @param Profile                     $profile
     * @param string                      $type
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function update(ProfileDocumentStore $store, CollaboratorDocumentRequest $request, Profile $profile, string $type): RedirectResponse
    {
        /** @var DocumentType $docType */
        $docType = DocumentType::fromField($type);

        /** @var UploadedFile $file */
        $file = $request->file($type);

        $document = $store->put($docType, $profile, $file);

        if ($docType === DocumentType::CONTRACT) {
            $profile->update($request->only(['contract_signed']));
            $document->update([
                'subtype' => $request->get('contract_type'),
                'approved_at' => now(),
                'active' => true,
            ]);

            if ($request->get('contract_signed') && !$profile->user_id) {
                $profile->load(['application']);

                /** @var CollaboratorApplication $application */
                $application = $profile->application;
                Mail::to($application->email)->send(new ApplicationNextStepsMail($application));
            }
        }

        $this->notifySuccessfulUpdate();

        $target = URL::previous();

        $target = str_contains($target, 'tab=documents') ? $target : "{$target}?tab=documents";

        /** @var RedirectResponse */
        return redirect($target);
    }

    /**
     * Validate the provided document.
     *
     * @param CollaboratorDocumentValidationRequest $request
     * @param ProfileDocument                       $document
     *
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function review(CollaboratorDocumentValidationRequest $request, ProfileDocument $document): RedirectResponse
    {
        $document->load(['profile']);

        DB::transaction(function () use ($document, $request) {
            if ($document->type === DocumentType::VISA) {
                $document->profile->update($request->only([
                    'restricted_visa', 'restriction_type', 'visa_hours', 'visa_available_hours',
                ]));
            }

            $document->update([
                ...$request->only(['subtype', 'valid_from', 'valid_to', 'comment']),
                'active' => true,
                'approved_at' => now(),
            ]);
        });

        $this->notifySuccessfulUpdate();

        $target = URL::previous();

        $target = str_contains($target, 'tab=documents') ? $target : "{$target}?tab=documents";

        /** @var RedirectResponse */
        return redirect($target);
    }
}
