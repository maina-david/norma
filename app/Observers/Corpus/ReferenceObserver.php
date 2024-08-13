<?php

namespace App\Observers\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;

class ReferenceObserver
{
    /**
     * Listen to the saved event.
     *
     * @param Reference $reference
     *
     * @return void
     */
    public function saved(Reference $reference): void
    {
        if ($reference->isDirty('parent_id')) {
            $this->updateTaggables($reference);
        }
    }

    /**
     * Update the inheritable taggables.
     *
     * @param Reference $reference
     *
     * @return void
     */
    protected function updateTaggables(Reference $reference): void
    {
        $reference->load([
            'locations:id', 'legalDomains:id', 'parentReference.locations:id', 'parentReference.legalDomains:id',
        ]);

        $reference->locations->each(function ($location) use ($reference) {
            /** @var Location $location */
            $reference->locations()->detach($location->id);
        });
        $reference->legalDomains->each(function ($domain) use ($reference) {
            /** @var LegalDomain $domain */
            $reference->legalDomains()->detach($domain->id);
        });

        if ($reference->parentReference) {
            $reference->parentReference->locations->each(function ($location) use ($reference) {
                /** @var Location $location */
                $reference->locations()->attach($location->id);
            });
            $reference->parentReference->legalDomains->each(function ($domain) use ($reference) {
                /** @var LegalDomain $domain */
                $reference->legalDomains()->attach($domain->id);
            });
        }
    }
}
