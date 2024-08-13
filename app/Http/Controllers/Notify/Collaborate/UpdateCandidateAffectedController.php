<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Actions\Notify\LegalUpdate\SyncMaintainedWorks;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notify\UpdateCandidateAffectedRequest;
use App\Models\Corpus\Doc;
use App\Models\Notify\UpdateCandidate;
use App\Models\Notify\UpdateCandidateLegislation;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;

class UpdateCandidateAffectedController extends Controller
{
    /**
     * Return the view.
     *
     * @codeCoverageIgnore
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function index(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load('updateCandidate.legislation.handovers');

        return singleTurboStreamResponse("doc-affected-{$doc->id}")
            ->view('pages.corpus.collaborate.doc.partials.affected-column', ['row' => $doc]);
    }

    /**
     * Get the form.
     *
     * @param Doc $doc
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse
     */
    public function create(Doc $doc): MultiplePendingTurboStreamResponse
    {
        $doc->load([
            'updateCandidate.legislation.handovers',
            'updateCandidate.legislation.catalogueDoc',
            'updateCandidate.legislation.work',
            'docMeta',
        ]);

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("doc-affected-{$doc->id}-form")
                ->view('partials.notify.update-candidate.affected-legislation', ['resource' => $doc]),
            singleTurboStreamResponse("doc-affected-{$doc->id}-view-buttons", 'replace')
                ->view('pages.corpus.collaborate.doc.partials.affected-column-view-buttons', ['row' => $doc]),
            singleTurboStreamResponse("doc-affected-{$doc->id}-handover-buttons", 'replace')
                ->view('pages.corpus.collaborate.doc.partials.affected-column-handover-button', ['row' => $doc]),
        ]);
    }

    /**
     * Update the affected legislation.
     *
     * @param UpdateCandidateAffectedRequest $request
     * @param Doc                            $doc
     *
     * @return RedirectResponse
     */
    public function store(UpdateCandidateAffectedRequest $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate $candidate */
        $candidate = UpdateCandidate::where('doc_id', $doc->id)->firstOrFail();
        $legislation = collect($request->get('affected_legislation') ?? [])
            ->map(function ($item) use ($doc) {
                $item['id'] = $item['id'] ?? null;
                $item['doc_id'] = $doc->id;

                return $item;
            })
            ->all();

        $candidate->update(['affected_available' => !empty($legislation)]);

        if (!empty($legislation)) {
            UpdateCandidateLegislation::upsert($legislation, 'id');
        }

        $deleted = collect(explode(',', $request->get('deleted_legislation', '')))->filter()->all();

        if (!empty($deleted)) {
            // @codeCoverageIgnoreStart
            UpdateCandidateLegislation::whereIn('id', $deleted)->delete();
            // @codeCoverageIgnoreEnd
        }

        (new SyncMaintainedWorks())->handle($doc->id);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.affected.create', ['doc' => $doc->id]);
    }
}
