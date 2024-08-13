<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdentifyTocItemStreamController extends Controller
{
    public function toc(WorkExpression $expression, Request $request, Doc $doc, ?int $itemId = null): Response
    {
        $showing = (bool) $request->input('show', 1);
        $items = TocItem::where('doc_id', $doc->id)
            ->when(is_null($itemId), fn ($q) => $q->whereNull('parent_id'))
            ->when(!is_null($itemId), fn ($q) => $q->where('parent_id', $itemId))
            ->with(['contentResource'])
            ->withCount('children')
            ->addSelect([
                'has_reference' => Reference::whereColumn((new Reference())->qualifyColumn('uid'), (new TocItem())->qualifyColumn('uid'))
                    ->whereRelation('work.docs', 'id', '=', $doc->id)
                    ->select((new Reference())->qualifyColumn('id'))
                    ->limit(1),
            ])
            ->withCasts(['has_reference' => 'bool'])
            ->get();

        /** @var TocItem|null */
        $tocItem = $itemId
            ? TocItem::withCount('children')
                ->with(['contentResource'])
                ->findOrFail($itemId)
            : null;

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.collaborate.toc-item.identify-tree-item',
            'target' => 'doc-toc-item-' . $doc->id . '-' . ($itemId ?? 'root'),
            'doc' => $doc,
            'showingChildren' => $showing,
            'items' => $items,
            'tocItem' => $tocItem,
            'resource' => is_null($tocItem) ? null : $tocItem->contentResource,
            'expression' => $expression,
        ]);

        return turboStreamResponse($view);
    }
}
