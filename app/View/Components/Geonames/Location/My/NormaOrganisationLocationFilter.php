<?php

namespace App\View\Components\Geonames\Location\My;

use App\Traits\Geonames\UsesNormaOrOrganisationLocationsAndDomains;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class NormaOrganisationLocationFilter extends Component
{
    use UsesNormaOrOrganisationLocationsAndDomains;

    /**
     * @param array<string, mixed>                                                    $applied
     * @param \Illuminate\Support\Collection<int, \App\Models\Geonames\Location>|null $locations
     */
    public function __construct(public array $applied = [], public ?Collection $locations = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $this->locations ??= $this->getAllUsableLocations(true);

        /** @var View */
        return view('components.geonames.location.my.norma-organisation-location-filter');
    }
}
