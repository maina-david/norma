<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Geonames\UsesLibryoOrOrganisationLocationsAndDomains;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RequirementsSetup extends Component
{
    use WithPagination;
    use UsesLibryoOrOrganisationLocationsAndDomains;

    /** @var array<string, mixed> */
    #[Url]
    public array $filters = [];

    /** @var \Illuminate\Support\Collection<int, \App\Models\Geonames\Location> */
    public Collection $locations;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Ontology\LegalDomain> */
    public Collection $domains;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Customer\Libryo> */
    public Collection $libryos;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->libryos = $this->getUsableLibryos();
        $this->domains = $this->getAllUsableLegalDomains($this->libryos);
        $this->locations = $this->getAllUsableLocations(true, $this->libryos);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        $manager = app(ActiveLibryosManager::class);

        /** @var View */
        return view('livewire.compilation.context-question.requirements-setup', [
            'isSingleMode' => $manager->isSingleMode(),
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return void
     */
    #[On('filtered')]
    public function handleFilter(array $filters): void
    {
        $this->filters = $filters;
    }
}
