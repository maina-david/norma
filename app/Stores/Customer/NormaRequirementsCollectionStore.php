<?php

namespace App\Stores\Customer;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Norma;
use App\Stores\Traits\AttachesDetaches;

class NormaRequirementsCollectionStore
{
    use AttachesDetaches;

    /**
     * Once we don't use the location_id anymore, this can be removed.
     * But for now it syncs all location ancestors of the given Norma's location
     * to the NormaRequirementsCollection pivot table.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function syncCollectionsFromLocationId(Norma $norma): void
    {
        $norma->load(['requirementsCollection.ancestorsWithSelf']);
        /** @var RequirementsCollection|null $requirementsCollection */
        $requirementsCollection = $norma->requirementsCollection;
        if (!$requirementsCollection) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $norma->requirementsCollections()->sync($requirementsCollection->ancestorsWithSelf->modelKeys());
    }
}
