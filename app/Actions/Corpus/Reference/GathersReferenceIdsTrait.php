<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Corpus\Reference;

trait GathersReferenceIdsTrait
{
    /**
     * @param array<int> $referenceIds
     * @param bool       $inherit
     *
     * @return array<int>
     */
    protected function gatherReferenceIds(array $referenceIds, bool $inherit = true): array
    {
        if ($inherit) {
            $workReference = Reference::whereKey($referenceIds)->typeWork()->first();
            if ($workReference) {
                $query = Reference::whereKey($workReference->id)
                    ->with('descendants:id');
            } else {
                $query = Reference::whereKey($referenceIds)
                    ->with('descendants:id');
            }
        } else {
            $query = Reference::whereKey($referenceIds);
        }

        $refsToTag = [];
        $query->chunk(2, function ($references) use (&$refsToTag, $inherit) {
            foreach ($references as $ref) {
                $refsToTag[$ref->id] = true;
                if (!$inherit) {
                    continue;
                }
                foreach ($ref->descendants as $descendant) {
                    $refsToTag[$descendant->id] = true;
                }
            }
        });

        /** @var array<int> */
        return array_keys($refsToTag);
    }
}
