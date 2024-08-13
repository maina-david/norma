<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceRequirement;
use App\Models\Requirements\ReferenceRequirementDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceRequirementController extends WorkflowTaskController
{
    /**
     * Create a new requirement update.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function store(Request $request, WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $data = [
            'reference_id' => $reference->id,
            'applied_by' => $request->user()?->id,
            'approved_by' => null,
            'annotation_source_id' => null,
        ];
        // ReferenceRequirement::updateOrCreate(['reference_id' => $reference->id], $data);
        ReferenceRequirementDraft::updateOrCreate(['reference_id' => $reference->id], [
            ...$data,
            'change_status' => RefRequirementChangeStatus::ADD->value,
        ]);

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'expression' => $expression->id,
            'reference' => $reference->id,
        ]);
    }

    /**
     * Apply the requirement update.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function update(Request $request, WorkExpression $expression, Reference $reference): RedirectResponse
    {
        /** @var ReferenceRequirementDraft|null */
        $draft = ReferenceRequirementDraft::find($reference->id);

        if ($draft) {
            ReferenceRequirement::updateOrCreate([
                'reference_id' => $reference->id,
            ], [
                'reference_id' => $reference->id,
                'applied_by' => $draft->applied_by,
                'approved_by' => $request->user()?->id,
                'annotation_source_id' => $draft->annotation_source_id,
            ]);

            $draft->delete();
        }

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'expression' => $expression->id,
            'reference' => $reference->id,
        ]);
    }

    /**
     * Delete the draft requirement.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function destroy(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $task = $this->task();

        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('collaborate.corpus.reference.requirement.delete') || ($task && $task->user_id === $user->id), 404);

        ReferenceRequirementDraft::where('reference_id', $reference->id)->delete();

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'reference' => $reference->id,
            'expression' => $expression->id,
        ]);
    }

    /**
     * Delete the requirement and the draft.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function forceDelete(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        ReferenceRequirement::where('reference_id', $reference->id)->delete();
        ReferenceRequirementDraft::where('reference_id', $reference->id)->delete();

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'reference' => $reference->id,
            'expression' => $expression->id,
        ]);
    }
}
