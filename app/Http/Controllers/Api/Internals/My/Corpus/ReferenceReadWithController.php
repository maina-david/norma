<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\JsonResponse;

class ReferenceReadWithController
{
    /**
     * Get the counts.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     * @param \App\Models\Corpus\Reference                $reference
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ActiveLibryosManager $manager, Reference $reference): JsonResponse
    {
        $libryo = $manager->getActive();

        $reference = $reference->loadCount(['raisesConsequenceGroups' => fn ($query) => $query->forLibryoLocations($libryo)]);
        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->forLibryoLocations($libryo)
            ->count();
        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->forLibryoLocations($libryo)
            ->count();

        return response()->json([
            'data' => [
                'amendments' => $amendments,
                'read_with' => $readWiths,
                'consequences' => $reference->raises_consequence_groups_count,
            ],
        ]);
    }
}
