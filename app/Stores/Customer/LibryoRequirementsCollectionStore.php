<?php

namespace App\Stores\Customer;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Libryo;
use App\Stores\Traits\AttachesDetaches;

class LibryoRequirementsCollectionStore
{
    use AttachesDetaches;

    /**
     * Once we don't use the location_id anymore, this can be removed.
     * But for now it syncs all location ancestors of the given Libryo's location
     * to the LibryoRequirementsCollection pivot table.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function syncCollectionsFromLocationId(Libryo $libryo): void
    {
        $libryo->load(['requirementsCollection.ancestorsWithSelf']);
        /** @var RequirementsCollection|null $requirementsCollection */
        $requirementsCollection = $libryo->requirementsCollection;
        if (!$requirementsCollection) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $libryo->requirementsCollections()->sync($requirementsCollection->ancestorsWithSelf->modelKeys());
    }
}
