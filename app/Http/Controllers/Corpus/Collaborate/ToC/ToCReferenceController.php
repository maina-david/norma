<?php

namespace App\Http\Controllers\Corpus\Collaborate\ToC;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Corpus\Collaborate\WorkflowTaskController;
use App\Jobs\Corpus\GenerateReferences;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceSelector;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\ReferenceTitleText;
use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\WorkExpressionContentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ToCReferenceController extends WorkflowTaskController
{
    /**
     * Get the references for the given volume.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @return JsonResponse
     */
    public function index(WorkExpression $expression, int $volume): JsonResponse
    {
        $this->abortNotJson();

        $references = Reference::where('work_id', $expression->work_id)
            ->where('type', ReferenceType::citation()->value)
            ->where('volume', $volume)
            ->with(['citation:id,type', 'refSelector:reference_id,selectors', 'refPlainText:reference_id,plain_text'])
            ->orderBy('start')
            ->select([
                'id', 'start', 'level', 'status', 'is_section', 'created_at',
                'updated_at', 'volume',
            ])
            ->paginate(100);

        return response()->json($references);
    }

    /**
     * Generate citations for the given expression.
     *
     * @param WorkExpression $expression
     *
     * @return JsonResponse
     */
    public function generate(WorkExpression $expression): JsonResponse
    {
        $this->abortNotJson();

        GenerateReferences::dispatch($expression);

        Cache::put($expression->citationsBatchCacheKey(), true, now()->addHours(4));

        return response()->json([]);
    }

    /**
     * Get the invalid volume markers.
     *
     * @param WorkExpressionContentManager $manager
     * @param WorkExpression               $expression
     *
     * @return JsonResponse
     */
    public function invalidVolumes(WorkExpressionContentManager $manager, WorkExpression $expression): JsonResponse
    {
        $invalid = $manager->validateVolumeMarkers($expression);

        return response()->json([
            'invalid' => $invalid,
        ]);
    }

    /**
     * Apply the references in the work.
     *
     * @param WorkExpression $expression
     *
     * @return JsonResponse
     */
    public function applyAll(WorkExpression $expression): JsonResponse
    {
        $this->abortNotJson();

        Reference::where('work_id', $expression->work_id)
            ->where('status', ReferenceStatus::pending()->value)
            ->update(['status' => ReferenceStatus::active()->value]);

        return response()->json([]);
    }

    /**
     * Delete the reference.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return JsonResponse
     */
    public function delete(WorkExpression $expression, Reference $reference): JsonResponse
    {
        $this->abortNotJson();

        if ($reference->work_id === $expression->work_id) {
            $reference->delete();
        }

        return response()->json([]);
    }

    /**
     * Delete the given references.
     *
     * @codeCoverageIgnore
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return JsonResponse
     */
    public function bulkDelete(Request $request, WorkExpression $expression): JsonResponse
    {
        $this->abortNotJson();
        $request->validate(['data' => ['required', 'array']]);

        Reference::where('work_id', $expression->work_id)
            ->whereKey($request->get('data'))
            ->get()
            ->each(function ($reference) {
                $reference->delete();
            });

        return response()->json([]);
    }

    /**
     * Update the reference details.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return JsonResponse
     */
    public function update(Request $request, WorkExpression $expression, Reference $reference): JsonResponse
    {
        $validated = $request->validate([
            'selectors' => ['required', 'array'],
            'title' => ['required', 'string'],
            'text' => ['required', 'string'],
            'start' => ['required', 'int'],
            'level' => ['required', 'int'],
            'is_section' => ['required', 'bool'],
            'volume' => ['required', 'int'],
        ]);

        $selectors = $validated['selectors'];
        $text = $validated['text'];
        $title = $validated['title'];
        unset($validated['selectors'], $validated['text'], $validated['title']);

        $reference->update($validated);

        ReferenceSelector::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'selectors' => $selectors,
        ]);

        ReferenceTitleText::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'text' => $text,
        ]);

        ReferenceText::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'plain_text' => $title,
        ]);

        $reference->load(['refSelector', 'refTitleText']);

        $data = [
            ...$reference->toArray(),
            ...['is_section' => $reference->is_section ? 1 : 0],
            'title' => $title,
            'text' => $text,
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Check the status of the generating batch.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Corpus\WorkExpression $expression
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBatchStatus(WorkExpression $expression): JsonResponse
    {
        $batchId = Cache::get($expression->citationsBatchCacheKey());

        if (!$batchId) {
            return response()->json(['data' => []]);
        }

        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            // the job has been pushed to the queue but we are waiting for it to be picked up.
            return response()->json(['data' => ['retry' => true]]);
        }

        if ($batch->cancelled() || $batch->finished()) {
            Cache::forget($expression->citationsBatchCacheKey());
        }

        return response()->json(['data' => $batch->toArray()]);
    }
}
