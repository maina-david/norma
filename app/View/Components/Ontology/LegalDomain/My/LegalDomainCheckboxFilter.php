<?php

namespace App\View\Components\Ontology\LegalDomain\My;

use App\Traits\Geonames\UsesLibryoOrOrganisationLocationsAndDomains;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class LegalDomainCheckboxFilter extends Component
{
    use UsesLibryoOrOrganisationLocationsAndDomains;

    /**
     * @param array<string, mixed>                                                       $applied
     * @param \Illuminate\Support\Collection<int, \App\Models\Ontology\LegalDomain>|null $domains
     */
    public function __construct(public array $applied = [], public ?Collection $domains = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $this->domains ??= $this->getAllUsableLegalDomains();

        /** @var View */
        return view('components.ontology.legal-domain.my.legal-domain-checkbox-filter');
    }
}
