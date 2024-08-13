<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Actions\Corpus\Reference\BulkInsertNewReferencesFromTocItems;
use App\Actions\Corpus\Reference\UpdateReferenceFromTocItem;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Internals\Collaborate\Corpus\TocItemResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TocItemController extends AbstractApiController
{
    /**
     * {@inheritDoc}
     */
    protected function getModelClass(): string
    {
        return TocItem::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiResourceClass(): string
    {
        return TocItemResource::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(Request $request): Builder
    {
        $parent = $request->route('item');
        $expression = $request->route('expression');
        $expression = WorkExpression::has('doc')->findOrFail($expression);

        return parent::getQuery($request)
            ->select(['id', 'uri_fragment', 'label', 'level', 'doc_id', 'content_resource_id', 'uri_fragment', 'requirement_score'])
            ->addSelect([
                'has_reference' => Reference::whereColumn((new Reference())->qualifyColumn('uid'), (new TocItem())->qualifyColumn('uid'))
                    ->whereRelation('work.docs', 'id', '=', $expression->doc_id)
                    ->select((new Reference())->qualifyColumn('id'))
                    ->limit(1),
            ])
            ->withCasts(['has_reference' => 'bool'])
            ->reorder('doc_position')
            ->where('doc_id', $expression->doc_id)
            ->when($parent, fn ($query) => $query->where('parent_id', $parent))
            ->when(!$parent, fn ($query) => $query->whereNull('parent_id'))
            ->withCount(['children']);
    }

    /**
     * Create a new reference from the given ToCItem.
     *
     * @param WorkExpression                    $expression
     * @param TocItem                           $item
     * @param \App\Models\Corpus\Reference|null $reference
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertFromTocItem(WorkExpression $expression, TocItem $item, ?Reference $reference = null): JsonResponse
    {
        app(BulkInsertNewReferencesFromTocItems::class)->handle([$item->id], $expression->work_id, $reference);

        return response()->json([]);
    }

    /**
     * Create a new reference from the given ToCItem.
     *
     * @param \Illuminate\Http\Request          $request
     * @param WorkExpression                    $expression
     * @param \App\Models\Corpus\Reference|null $reference
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkInsertFromTocItem(
        Request $request,
        WorkExpression $expression,
        ?Reference $reference = null
    ): JsonResponse {
        $request->validate(['toc_items' => ['required', 'array']]);

        /** @var array<int, int> $tocItems */
        $tocItems = $request->get('toc_items', []);

        app(BulkInsertNewReferencesFromTocItems::class)->handle($tocItems, $expression->work_id, $reference);

        return response()->json([]);
    }

    /**
     * Update the active reference from the given ToCItem.
     *
     * @param WorkExpression               $expression
     * @param TocItem                      $item
     * @param \App\Models\Corpus\Reference $reference
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFromTocItem(WorkExpression $expression, TocItem $item, Reference $reference): JsonResponse
    {
        if ($reference->work_id === $expression->work_id) {
            app(UpdateReferenceFromTocItem::class)->handle($item, $reference, request()->user());
        }

        return response()->json([]);
    }
}
