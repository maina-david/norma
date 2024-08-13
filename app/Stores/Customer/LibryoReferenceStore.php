<?php

namespace App\Stores\Customer;

use App\Models\Customer\Libryo;
use App\Stores\Traits\AttachesDetaches;

class LibryoReferenceStore
{
    use AttachesDetaches;

    /**
     * Attach if don't exist, and detach references that don't exist.
     *
     * @param Libryo     $libryo
     * @param array<int> $referenceIds
     *
     * @return void
     */
    public function syncReferences(Libryo $libryo, array $referenceIds): void
    {
        $libryo->compiledReferences()->sync($referenceIds);
    }

    /**
     * Attach if don't exist, and detach references that don't exist.
     *
     * @param Libryo     $libryo
     * @param array<int> $referenceIds
     *
     * @return void
     */
    public function syncLiveReferences(Libryo $libryo, array $referenceIds): void
    {
        $libryo->references()->sync($referenceIds);
    }
}
