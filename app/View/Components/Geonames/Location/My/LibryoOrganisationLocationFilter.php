<?php

namespace App\View\Components\Geonames\Location\My;

use App\Traits\Geonames\UsesLibryoOrOrganisationLocationsAndDomains;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class LibryoOrganisationLocationFilter extends Component
{
    use UsesLibryoOrOrganisationLocationsAndDomains;

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
        return view('components.geonames.location.my.libryo-organisation-location-filter');
    }
}
