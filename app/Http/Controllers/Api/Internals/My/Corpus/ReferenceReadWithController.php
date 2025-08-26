<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\JsonResponse;

class ReferenceReadWithController
{
    /**
     * Get the counts.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     * @param \App\Models\Corpus\Reference                $reference
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ActiveNormasManager $manager, Reference $reference): JsonResponse
    {
        $norma = $manager->getActive();

        $reference = $reference->loadCount(['raisesConsequenceGroups' => fn ($query) => $query->forNormaLocations($norma)]);
        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->forNormaLocations($norma)
            ->count();
        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->forNormaLocations($norma)
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
