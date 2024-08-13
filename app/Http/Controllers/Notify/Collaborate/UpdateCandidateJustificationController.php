<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Notify\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notify\UpdateCandidateJustificationRequest;
use App\Models\Corpus\Doc;
use App\Models\Notify\UpdateCandidate;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UpdateCandidateJustificationController extends Controller
{
    /**
     * Return the views.
     *
     * @param Doc $doc
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function index(Doc $doc): MultiplePendingTurboStreamResponse
    {
        $doc->load('updateCandidate');

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("doc-justification-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.justification-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-affected-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.affected-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-checked-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.checked-column', ['row' => $doc]),
        ]);
    }

    /**
     * Show the justification form.
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function create(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load('updateCandidate');

        return singleTurboStreamResponse("doc-justification-{$doc->id}-form")
            ->view('partials.notify.update-candidate.justification', ['resource' => $doc]);
    }

    /**
     * Update the candidate.
     *
     * @param UpdateCandidateJustificationRequest $request
     * @param Doc                                 $doc
     *
     * @return RedirectResponse
     */
    public function store(UpdateCandidateJustificationRequest $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate|null $candidate */
        $candidate = UpdateCandidate::where('doc_id', $doc->id)->first();

        if ($candidate) {
            $candidate->update([
                'justification_status' => $request->get('justification_status'),
                'justification' => $request->get('justification'),
            ]);

            $this->notifySuccessfulUpdate();

            return redirect()->route('collaborate.docs-for-update.justification.index', ['doc' => $doc->id]);
        }

        UpdateCandidate::create([
            'doc_id' => $doc->id,
            'manual' => false,
            'justification_status' => $request->get('justification_status'),
            'justification' => $request->get('justification'),
            'notification_status' => NotificationStatus::UNKNOWN->value,
            'checked_comment' => '',
            'affected_legislation' => [],
            'affected_available' => false,
            'author_id' => Auth::id(),
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.justification.index', ['doc' => $doc->id]);
    }
}
