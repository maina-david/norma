<?php

namespace App\Traits\Geonames;

use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\NormaRequirementsCollection;
use App\Models\Geonames\Location;
use App\Models\Geonames\Pivots\LocationLocation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait UsesNormaOrOrganisationLocationsAndDomains
{
    /**
     * Get the norma sub query that returns the given field.
     *
     * @param string $field
     *
     * @return \Illuminate\Database\Eloquent\Builder|array<int, int>
     */
    protected function getNormaFieldSubQuery(string $field): Builder|array
    {
        $manager = app(ActiveNormasManager::class);

        /** @var \App\Models\Customer\Norma $norma */
        $norma = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        return $manager->isSingleMode() ? [$norma->{$field}] : $organisation->normas()->select([$field])->getQuery();
    }

    /**
     * Get the usable Normas.
     *
     * @return \Illuminate\Support\Collection<int, Norma>
     */
    protected function getUsableNormas(): Collection
    {
        $manager = app(ActiveNormasManager::class);

        /** @var \App\Models\Customer\Norma|null $norma */
        $norma = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        if ($norma) {
            $norma->load(['compilationSetting']);

            return collect([$norma]);
        }

        return Norma::with(['compilationSetting'])->where('organisation_id', $organisation->id)->get();
    }

    /**
     * Get all the locations for the given application state.
     *
     * @param bool                                             $withAncestors
     * @param \Illuminate\Support\Collection<int, Norma>|null $normas
     *
     * @return \Illuminate\Support\Collection<int, Location>
     */
    protected function getAllUsableLocations(bool $withAncestors = false, ?Collection $normas = null): Collection
    {
        $normas ??= $this->getUsableNormas();
        $locations = $normas->pluck('location_id');

        $withCollections = $normas->filter(fn ($norma) => $norma->compilationSetting->use_collections ?? false)
            ->pluck('id')
            ->all();

        if (!empty($withCollections)) {
            $withCollections = NormaRequirementsCollection::whereIn('place_id', $withCollections)
                ->pluck('collection_id')
                ->all();

            $locations = $locations->merge($withCollections)->unique()->values();
        }

        $locations = $locations->unique()->values()->all();

        if ($withAncestors) {
            $locations = LocationLocation::whereIn('descendant', $locations)->select(['ancestor']);
        }

        return Location::whereIn('id', $locations)->orderBy('title')->get(['id', 'title']);
    }

    /**
     * Get all Legal Domains for the given application state.
     *
     * @param \Illuminate\Support\Collection<int, Norma>|null $normas
     *
     * @return \Illuminate\Support\Collection<int, LegalDomain>
     */
    protected function getAllUsableLegalDomains(?Collection $normas = null): Collection
    {
        $subQuery = ($normas ?? $this->getUsableNormas())->pluck('id')->all();

        return LegalDomain::whereNull('archived_at')
            ->whereHas('normas', fn ($query) => $query->whereIn('id', $subQuery))
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}
