<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;

use function HotwiredLaravel\TurboLaravel\dom_id;

class ReferenceSummaryController extends WorkflowTaskController
{
    /**
     * Create a new summary or summary update.
     *
     * @param Reference $reference
     *
     * @return RedirectResponse
     */
    public function store(Reference $reference): RedirectResponse
    {
        // don't create a new summary, only a new draft
        /** @var Summary $summary */
        $summary = Summary::firstOrNew(['reference_id' => $reference->id]);

        /** @var ReferenceSummaryDraft $summaryDraft */
        $summaryDraft = ReferenceSummaryDraft::firstOrCreate(['reference_id' => $reference->id]);
        $summaryDraft->summary_body = $summary->summary_body;
        $summaryDraft->save();

        return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id, 'tab' => 'draft']);
    }

    /**
     * Get the summary for the given reference.
     *
     * @param Reference $reference
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function show(Reference $reference): MultiplePendingTurboStreamResponse
    {
        $counts = ['linkedParents', 'linkedChildren'];

        $isPreviewing = false;
        $previewing = $this->getPreviewing();

        if ($previewing->isNotEmpty()) {
            // @codeCoverageIgnoreStart
            $expressions = WorkExpression::where('work_id', $reference->work_id)->pluck('id');
            $isPreviewing = $expressions->intersect($previewing)->isNotEmpty();
            // @codeCoverageIgnoreEnd
        }

        if ($this->task()) {
            $counts[] = 'collaborateComments';
        }

        $reference->load(['summary', 'summaryDraft'])->loadCount($counts);

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("reference_{$reference->id}_icons", 'replace')
                ->view('partials.corpus.reference.collaborate.reference-icons', ['reference' => $reference, 'previewing' => $isPreviewing]),
            singleTurboStreamResponse(dom_id($reference, 'summary'))
                ->view('partials.corpus.summary.collaborate.reference-summary', ['reference' => $reference, 'previewing' => $isPreviewing]),
        ]);
    }

    /**
     * Delete the summary of the reference.
     *
     * @param Reference $reference
     *
     * @return RedirectResponse
     */
    public function destroy(Reference $reference): RedirectResponse
    {
        $reference->load(['summary', 'summaryDraft']);

        if ($summary = $reference->summary) {
            $summary->delete();
        }

        $reference->summaryDraft?->delete();

        return redirect()->route('collaborate.corpus.summary.show', ['reference' => $reference->id]);
    }
}
