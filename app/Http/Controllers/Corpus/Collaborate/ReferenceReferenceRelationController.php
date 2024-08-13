<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Requests\Corpus\ReferenceReferenceRequest;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Stores\Corpus\ReferenceReferenceStore;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;

class ReferenceReferenceRelationController extends WorkflowTaskController
{
    /**
     * Attach a new relation.
     *
     * @param ReferenceReferenceRequest $request
     * @param WorkExpression            $expression
     * @param Reference                 $reference
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function store(ReferenceReferenceRequest $request, WorkExpression $expression, Reference $reference): RedirectResponse
    {
        $this->abortNotTurbo();
        $task = $this->task();

        $this->authorize('annotate', [$expression, $task]);

        $store = new ReferenceReferenceStore();
        $method = str_starts_with($request->get('link_type'), '-') ? 'attachChildren' : 'attachParents';
        $linkType = (int) str_replace('-', '', $request->get('link_type'));

        Reference::whereKey($request->get('references', []))
            ->get(['id', 'work_id'])
            ->each(function (Reference $related) use ($linkType, $method, $reference, $store) {
                $store->{$method}($reference, collect([$related]), [
                    'parent_work_id' => $method === 'attachChildren' ? $reference->work_id : $related->work_id,
                    'child_work_id' => $method === 'attachChildren' ? $related->work_id : $reference->work_id,
                    'link_type' => $linkType,
                ]);
            });

        return redirect()->route('collaborate.work-expressions.references.index', [
            ...$this->requestQuery(),
            'expression' => $expression->id,
            'activate' => $reference,
        ]);
    }

    /**
     * Remove the relationship.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $related
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function deleteChild(WorkExpression $expression, Reference $reference, string $related): RedirectResponse
    {
        return $this->delete($expression, $reference, $related, 'linkedChildren');
    }

    /**
     * Remove the relationship.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $related
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function deleteParent(WorkExpression $expression, Reference $reference, string $related): RedirectResponse
    {
        return $this->delete($expression, $reference, $related, 'linkedParents');
    }

    /**
     * Remove the relationship.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $related
     * @param string         $relationship
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function delete(WorkExpression $expression, Reference $reference, string $related, string $relationship): RedirectResponse
    {
        $this->abortNotTurbo();
        $task = $this->task();

        $this->authorize('annotate', [$expression, $task]);

        /** @var Reference|null $related */
        $related = Reference::find($related);

        $reference->{$relationship}()->detach($related->id ?? null);

        return redirect()->route('collaborate.work-expressions.references.index', [
            ...$this->requestQuery(),
            'activate' => $reference,
            'expression' => $expression->id,
        ]);
    }
}
