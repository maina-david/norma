<?php

namespace App\Http\Controllers\Corpus;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TocItemStreamController extends Controller
{
    public function toc(Request $request, Doc $doc, ?int $itemId = null): Response
    {
        $showing = (bool) $request->input('show', 1);
        $items = TocItem::where('doc_id', $doc->id)
            ->when(is_null($itemId), fn ($q) => $q->whereNull('parent_id'))
            ->when(!is_null($itemId), fn ($q) => $q->where('parent_id', $itemId))
            ->with(['contentResource'])
            ->withCount('children')
            ->get();

        /** @var TocItem|null */
        $tocItem = $itemId
            ? TocItem::withCount('children')
                ->with(['contentResource'])
                ->findOrFail($itemId)
            : null;

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.toc-item.tree-item',
            'target' => 'doc-toc-item-' . $doc->id . '-' . ($itemId ?? 'root'),
            'doc' => $doc,
            'showingChildren' => $showing,
            'items' => $items,
            'tocItem' => $tocItem,
            'resource' => is_null($tocItem) ? null : $tocItem->contentResource,
        ]);

        return turboStreamResponse($view);
    }
}
