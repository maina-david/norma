<?php

namespace App\Stores\Customer;

use App\Models\Customer\Norma;
use App\Stores\Traits\AttachesDetaches;

class NormaReferenceStore
{
    use AttachesDetaches;

    /**
     * Attach if don't exist, and detach references that don't exist.
     *
     * @param Norma     $norma
     * @param array<int> $referenceIds
     *
     * @return void
     */
    public function syncReferences(Norma $norma, array $referenceIds): void
    {
        $norma->compiledReferences()->sync($referenceIds);
    }

    /**
     * Attach if don't exist, and detach references that don't exist.
     *
     * @param Norma     $norma
     * @param array<int> $referenceIds
     *
     * @return void
     */
    public function syncLiveReferences(Norma $norma, array $referenceIds): void
    {
        $norma->references()->sync($referenceIds);
    }
}
