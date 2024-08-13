<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requirements\SummaryRequest;
use App\Models\Corpus\Reference;
use App\Models\Requirements\Summary;
use App\Services\HTMLPurifierService;
use Illuminate\Http\RedirectResponse;

class ReferenceDraftSummaryController extends Controller
{
    /**
     * Update the live summary using the draft summary.
     *
     * @param Reference $reference
     *
     * @return RedirectResponse
     */
    public function apply(Reference $reference): RedirectResponse
    {
        $reference->load(['summary']);

        if ($reference->summaryDraft) {
            $summaryDraft = $reference->summaryDraft;
            Summary::updateOrCreate(['reference_id' => $reference->id], [
                'summary_body' => $summaryDraft->summary_body,
            ]);
            $reference->summaryDraft->delete();
        }

        return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id]);
    }

    /**
     * Update the draft summary.
     *
     * @param SummaryRequest $request
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function update(SummaryRequest $request, Reference $reference): RedirectResponse
    {
        $reference->load(['summaryDraft']);

        if ($summaryDraft = $reference->summaryDraft) {
            $summaryDraft->update([
                'summary_body' => app(HTMLPurifierService::class)->cleanSummary($request->get('body')),
            ]);
        }

        return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id, 'tab' => 'draft']);
    }

    /**
     * Delete the draft summary of the reference.
     *
     * @param Reference $reference
     *
     * @return RedirectResponse
     */
    public function destroy(Reference $reference): RedirectResponse
    {
        $reference->load(['summary', 'summaryDraft']);

        if (!$reference->summary && !$reference->summaryDraft) {
            // @codeCoverageIgnoreStart
            return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id]);
            // @codeCoverageIgnoreEnd
        }

        // just need this for legacy where empty summaries were created together with a draft.
        // won't need in future
        if ($reference->summary && !$reference->summary->summary_body) {
            // @codeCoverageIgnoreStart
            $reference->summary->delete();
            $reference->summaryDraft?->delete();

            return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id]);
            // @codeCoverageIgnoreEnd
        }

        // otherwise delete draft
        $reference->summaryDraft?->delete();

        return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id]);
    }
}
