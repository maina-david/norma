<?php

namespace App\Stores\Corpus;

use App\Models\Corpus\Reference;
use App\Stores\Traits\AttachesDetaches;
use Exception;
use Illuminate\Support\Collection;

class ReferenceReferenceStore
{
    use AttachesDetaches;

    /**
     * Attach child references.
     *
     * @param Reference            $reference
     * @param Collection           $children
     * @param array<string, mixed> $pivot
     *
     * @throws Exception
     *
     * @return void
     */
    public function attachChildren(Reference $reference, Collection $children, array $pivot): void
    {
        $this->attachRelations($reference, 'linkedChildren', $children, $pivot);
    }

    /**
     * Attach parent references.
     *
     * @param Reference            $reference
     * @param Collection           $parents
     * @param array<string, mixed> $pivot
     *
     * @throws Exception
     *
     * @return void
     */
    public function attachParents(Reference $reference, Collection $parents, array $pivot): void
    {
        $this->attachRelations($reference, 'linkedParents', $parents, $pivot);
    }
}
