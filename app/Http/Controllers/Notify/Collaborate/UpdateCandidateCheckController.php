<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notify\UpdateCandidateCheckedRequest;
use App\Models\Corpus\Doc;
use App\Models\Notify\UpdateCandidate;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UpdateCandidateCheckController extends Controller
{
    /**
     * Return the view.
     *
     * @param Doc $doc
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function index(Doc $doc): MultiplePendingTurboStreamResponse
    {
        $doc->load('updateCandidate');

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("doc-checked-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.checked-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-notification-status-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.notification-status-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-notification-task-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.notification-task-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-additional-actions-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.additional-actions-column', ['row' => $doc]),
            singleTurboStreamResponse("doc-affected-{$doc->id}")
                ->view('pages.corpus.collaborate.doc.partials.affected-column', ['row' => $doc]),
        ]);
    }

    /**
     * Get the form.
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function create(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load('updateCandidate');

        return singleTurboStreamResponse("doc-checked-{$doc->id}-form")
            ->view('partials.notify.update-candidate.check-comment', ['resource' => $doc]);
    }

    /**
     * Update the checked status.
     *
     * @param UpdateCandidateCheckedRequest $request
     * @param Doc                           $doc
     *
     * @return RedirectResponse
     */
    public function store(UpdateCandidateCheckedRequest $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate $candidate */
        $candidate = UpdateCandidate::where('doc_id', $doc->id)->firstOrFail();

        $candidate->update([
            'check_sources_status' => $request->get('check_sources_status'),
            'checked_comment' => $request->get('checked_comment'),
            'checked_at' => now(),
            'checked_by_id' => Auth::id(),
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.check.index', ['doc' => $doc->id]);
    }
}
