<?php

namespace App\Stores\Customer;

use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class LibryoWorkStore
{
    use AttachesDetaches;

    /**
     * Find all the works associated with the given references (including parent works) and sync to libryo.
     *
     * @param Libryo          $libryo
     * @param array<int, int> $referenceIds
     *
     * @return void
     */
    public function syncWorksForReferences(Libryo $libryo, array $referenceIds): void
    {
        $allWorkIds = $this->getWorkIds($referenceIds);

        $libryo->compiledWorks()->sync($allWorkIds->values()->all());
    }

    /**
     * Find all the works associated with the given references (including parent works) and sync to libryo.
     *
     * @param Libryo          $libryo
     * @param array<int, int> $referenceIds
     *
     * @return void
     */
    public function syncLiveWorksForReferences(Libryo $libryo, array $referenceIds): void
    {
        $allWorkIds = $this->getWorkIds($referenceIds);

        $libryo->works()->sync($allWorkIds->values()->all());
    }

    /**
     * Extract the work IDs from the references.
     *
     * @param array<int, int> $referenceIds
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getWorkIds(array $referenceIds): Collection
    {
        $allWorkIds = collect([]);
        // create smaller chunks so we don't have massive DB queries
        collect($referenceIds)->chunk(1000)->each(function ($refIds) use (&$allWorkIds) {
            $workIds = Work::active()
                ->whereHas('references', fn ($q) => $q->whereKey($refIds))
                ->pluck('id');
            $allWorkIds = $allWorkIds->merge($workIds);
            // two separate queries is more efficient
            $workIds = Work::active()
                ->whereHas('children.references', fn ($q) => $q->whereKey($refIds))
                ->pluck('id')
                ->all();
            $allWorkIds = $allWorkIds->merge($workIds);
        });

        return $allWorkIds;
    }
}
