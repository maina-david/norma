<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Requests\Requirements\SummaryRequest;
use App\Models\Corpus\Reference;
use App\Models\Requirements\Summary;
use App\Services\HTMLPurifierService;
use Illuminate\Http\JsonResponse;

class ReferenceSummaryDraftController
{
    /**
     * Update the live summary using the draft summary.
     *
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function apply(Reference $reference): JsonResponse
    {
        $reference->load(['summary']);

        if ($reference->summaryDraft) {
            $summaryDraft = $reference->summaryDraft;
            Summary::updateOrCreate(['reference_id' => $reference->id], [
                'summary_body' => $summaryDraft->summary_body,
            ]);
            $reference->summaryDraft->delete();
        }

        return response()->json([]);
    }

    /**
     * Update the draft summary.
     *
     * @param SummaryRequest $request
     * @param Reference      $reference
     *
     * @return JsonResponse
     */
    public function update(SummaryRequest $request, Reference $reference): JsonResponse
    {
        $reference->load(['summaryDraft']);

        if ($summaryDraft = $reference->summaryDraft) {
            $summaryDraft->update([
                'summary_body' => app(HTMLPurifierService::class)->cleanSummary($request->get('body')),
            ]);
        }

        return response()->json([]);
    }

    /**
     * Delete the live and draft summary.
     *
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function delete(Reference $reference): JsonResponse
    {
        $reference->load(['summary', 'summaryDraft']);

        // just need this for legacy where empty summaries were created together with a draft.
        // won't need in future
        if ($reference->summary && !$reference->summary->summary_body) {
            // @codeCoverageIgnoreStart
            $reference->summary->delete();

            // @codeCoverageIgnoreEnd
        }

        $reference->summaryDraft?->delete();

        return response()->json([]);
    }
}
