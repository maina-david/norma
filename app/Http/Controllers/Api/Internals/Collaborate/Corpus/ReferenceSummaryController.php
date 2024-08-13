<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use Illuminate\Http\JsonResponse;

class ReferenceSummaryController
{
    /**
     * Create a new draft summary.
     *
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function store(Reference $reference): JsonResponse
    {
        // don't create a new summary, only a new draft
        /** @var Summary $summary */
        $summary = Summary::firstOrNew(['reference_id' => $reference->id]);

        /** @var ReferenceSummaryDraft $summaryDraft */
        $summaryDraft = ReferenceSummaryDraft::firstOrCreate(['reference_id' => $reference->id]);
        $summaryDraft->summary_body = $summary->summary_body;
        $summaryDraft->save();

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

        if ($summary = $reference->summary) {
            $summary->delete();
        }

        $reference->summaryDraft?->delete();

        return response()->json([]);
    }
}
