<?php

namespace App\Livewire\Ontology\Category;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Traits\Geonames\UsesLibryoOrOrganisationLocationsAndDomains;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryTree extends Component
{
    use UsesLibryoOrOrganisationLocationsAndDomains;
    use WithPagination;

    /** @var array<string, mixed> */
    public array $applied = [];

    /** @var \Illuminate\Support\Collection<int, \App\Models\Geonames\Location>|null */
    public ?Collection $locations = null;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Ontology\LegalDomain>|null */
    public ?Collection $domains = null;

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        $locations = ($this->locations ?? $this->getAllUsableLocations(true))->pluck('id')->all();
        $domains = ($this->domains ?? $this->getAllUsableLegalDomains())->pluck('id')->all();

        $nodes = CategorySelectorCache::treeViaQuery(function ($query) use ($locations, $domains) {
            $query->whereHas('references', function ($builder) use ($domains, $locations) {
                $builder->active()
                    ->compilable()
                    ->whereHas('locations', fn ($q) => $q->whereIn('id', $locations))
                    ->whereHas('legalDomains', fn ($q) => $q->whereIn('id', $domains));
            });
        });

        $nodes = collect($nodes);
        $industry = $nodes->where('id', 111000)->first();
        $nodes = $nodes->filter(fn ($item) => $item['id'] !== 111000)->values()->all();

        /** @var View */
        return view('livewire.ontology.category.category-tree', [
            'nodes' => $nodes,
            'industry' => $industry,
        ]);
    }
}
