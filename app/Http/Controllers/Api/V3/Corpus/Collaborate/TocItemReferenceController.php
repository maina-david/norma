<?php

namespace App\Http\Controllers\Api\V3\Corpus\Collaborate;

use App\Actions\Corpus\Reference\BulkInsertNewReferencesFromTocItems;
use App\Http\Resources\Internals\Collaborate\Corpus\ReferenceResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use Exception;

class TocItemReferenceController
{
    /**
     * Create a new reference from the ToC Item.
     *
     * @param \App\Models\Corpus\TocItem $item
     *
     * @throws Exception
     *
     * @return \App\Http\Resources\Internals\Collaborate\Corpus\ReferenceResource
     */
    public function store(TocItem $item): ReferenceResource
    {
        $item->loadMissing(['doc.work']);

        abort_unless(isset($item->doc->work->id), 422, 'The Toc Item is not attached to a work.');

        $exists = Reference::where('uid', $item->uid)
            ->where('work_id', $item->doc->work->id ?? null)
            ->first();

        if ($exists) {
            return new ReferenceResource($exists);
        }

        $created = app(BulkInsertNewReferencesFromTocItems::class)->handle([$item->id], $item->doc->work->id ?? 0);

        return new ReferenceResource($created[0]);
    }
}
