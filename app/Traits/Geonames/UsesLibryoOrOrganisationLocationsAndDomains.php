<?php

namespace App\Traits\Geonames;

use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\LibryoRequirementsCollection;
use App\Models\Geonames\Location;
use App\Models\Geonames\Pivots\LocationLocation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait UsesLibryoOrOrganisationLocationsAndDomains
{
    /**
     * Get the libryo sub query that returns the given field.
     *
     * @param string $field
     *
     * @return \Illuminate\Database\Eloquent\Builder|array<int, int>
     */
    protected function getLibryoFieldSubQuery(string $field): Builder|array
    {
        $manager = app(ActiveLibryosManager::class);

        /** @var \App\Models\Customer\Libryo $libryo */
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        return $manager->isSingleMode() ? [$libryo->{$field}] : $organisation->libryos()->select([$field])->getQuery();
    }

    /**
     * Get the usable Libryos.
     *
     * @return \Illuminate\Support\Collection<int, Libryo>
     */
    protected function getUsableLibryos(): Collection
    {
        $manager = app(ActiveLibryosManager::class);

        /** @var \App\Models\Customer\Libryo|null $libryo */
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        if ($libryo) {
            $libryo->load(['compilationSetting']);

            return collect([$libryo]);
        }

        return Libryo::with(['compilationSetting'])->where('organisation_id', $organisation->id)->get();
    }

    /**
     * Get all the locations for the given application state.
     *
     * @param bool                                             $withAncestors
     * @param \Illuminate\Support\Collection<int, Libryo>|null $libryos
     *
     * @return \Illuminate\Support\Collection<int, Location>
     */
    protected function getAllUsableLocations(bool $withAncestors = false, ?Collection $libryos = null): Collection
    {
        $libryos ??= $this->getUsableLibryos();
        $locations = $libryos->pluck('location_id');

        $withCollections = $libryos->filter(fn ($libryo) => $libryo->compilationSetting->use_collections ?? false)
            ->pluck('id')
            ->all();

        if (!empty($withCollections)) {
            $withCollections = LibryoRequirementsCollection::whereIn('place_id', $withCollections)
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
     * @param \Illuminate\Support\Collection<int, Libryo>|null $libryos
     *
     * @return \Illuminate\Support\Collection<int, LegalDomain>
     */
    protected function getAllUsableLegalDomains(?Collection $libryos = null): Collection
    {
        $subQuery = ($libryos ?? $this->getUsableLibryos())->pluck('id')->all();

        return LegalDomain::whereNull('archived_at')
            ->whereHas('libryos', fn ($query) => $query->whereIn('id', $subQuery))
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}
