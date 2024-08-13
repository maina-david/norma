<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Requirements\ReferenceRequirement;
use App\Models\Requirements\ReferenceRequirementDraft;
use App\Models\Workflows\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceRequirementController
{
    /**
     * Create a new requirement update.
     *
     * @param Request   $request
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function store(Request $request, Reference $reference): JsonResponse
    {
        $data = [
            'reference_id' => $reference->id,
            'applied_by' => $request->user()?->id,
            'approved_by' => null,
            'annotation_source_id' => null,
        ];

        $exists = ReferenceRequirement::where('reference_id', $reference->id)->exists();

        ReferenceRequirementDraft::updateOrCreate(['reference_id' => $reference->id], [
            ...$data,
            'change_status' => $exists ? RefRequirementChangeStatus::REMOVE->value : RefRequirementChangeStatus::ADD->value,
        ]);

        return response()->json([]);
    }

    /**
     * Apply the requirement update.
     *
     * @param Request   $request
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function update(Request $request, Reference $reference): JsonResponse
    {
        /** @var ReferenceRequirementDraft $draft */
        $draft = ReferenceRequirementDraft::find($reference->id);

        abort_unless((bool) $draft, 404);

        if ($draft->change_status === RefRequirementChangeStatus::REMOVE->value) {
            ReferenceRequirement::where('reference_id', $reference->id)->delete();
            $draft->delete();

            return response()->json([]);
        }

        ReferenceRequirement::updateOrCreate([
            'reference_id' => $reference->id,
        ], [
            'reference_id' => $reference->id,
            'applied_by' => $draft->applied_by,
            'approved_by' => $request->user()?->id,
            'annotation_source_id' => $draft->annotation_source_id,
        ]);

        $draft->delete();

        return response()->json([]);
    }

    /**
     * Delete the draft requirement.
     *
     * @param Reference $reference
     * @param Task|null $task
     *
     * @return JsonResponse
     */
    public function destroy(Reference $reference, ?Task $task): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('collaborate.corpus.reference.requirement.delete') || ($task && $task->user_id === $user->id), 404);

        ReferenceRequirementDraft::where('reference_id', $reference->id)->delete();

        return response()->json([]);
    }

    /**
     * Delete the requirement and the draft.
     *
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function forceDelete(Reference $reference): JsonResponse
    {
        ReferenceRequirement::where('reference_id', $reference->id)->delete();
        ReferenceRequirementDraft::where('reference_id', $reference->id)->delete();

        return response()->json([]);
    }
}
