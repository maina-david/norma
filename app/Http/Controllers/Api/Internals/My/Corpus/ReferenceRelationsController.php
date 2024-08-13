<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Http\Controllers\Traits\SelectsSummarisedReferenceFields;
use App\Http\Resources\Internals\My\Corpus\ReferenceResource;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceRelationsController
{
    use SelectsSummarisedReferenceFields;

    /**
     * Get the listing of the given relation.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     * @param \App\Models\Corpus\Reference                $reference
     * @param string                                      $relation
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveLibryosManager $manager, Reference $reference, string $relation): AnonymousResourceCollection
    {
        abort_unless(in_array($relation, ['amendments', 'read-withs', 'consequences']), 404);

        $libryo = $manager->getActive();

        $type = match ($relation) {
            'amendments' => ReferenceLinkType::AMENDMENT,
            'consequences' => ReferenceLinkType::CONSEQUENCE,
            default => ReferenceLinkType::READ_WITH,
        };

        $references = $this->applyReferenceSelector((new Reference())->newQuery())
            ->whereIn(qualify_column(Reference::class, 'id'), $reference->getLinkedTypeIDs($type))
            ->forLibryoLocations($libryo)
            ->paginate(min(request('perPage', 20), 20));

        return ReferenceResource::collection($references);
    }
}
