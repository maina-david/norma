<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReferenceConsequenceController extends WorkflowTaskController
{
    /**
     * Delete the draft consequence.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function destroy(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();
        $task = $this->task();

        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('collaborate.corpus.reference.consequence.delete') || ($task && $task->user_id === $user->id), 404);

        $reference->has_consequences_update = null;
        $reference->save();

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'expression' => $expression->id,
            'reference' => $reference->id,
        ]);
    }

    /**
     * Delete the consequence and the draft.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return RedirectResponse
     */
    public function forceDelete(WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();

        $reference->has_consequences = false;
        $reference->has_consequences_update = null;
        $reference->save();

        return redirect()->route('collaborate.work-expressions.references.activate', [
            'expression' => $expression->id,
            'reference' => $reference->id,
        ]);
    }
}
