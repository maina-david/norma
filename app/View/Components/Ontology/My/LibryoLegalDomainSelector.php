<?php

namespace App\View\Components\Ontology\My;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LibryoLegalDomainSelector extends Component
{
    /** @var array<mixed, string> */
    public array $legalDomains = [];

    /**
     * @param string                       $name
     * @param string                       $label
     * @param array<mixed>|string|int|null $value
     * @param bool                         $multiple
     */
    public function __construct(
        public string $name,
        public string $label = '',
        public array|string|int|null $value = null,
        public bool $multiple = false
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $query = LegalDomain::forLibryo($libryo)->forLocation($libryo->location_id);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $query = LegalDomain::forOrganisation($organisation);
        }

        $this->legalDomains = array_merge(
            [['label' => '--', 'value' => '']],
            $query
                ->active()
                ->get()
                ->map(fn ($domain) => ['label' => $domain->title, 'value' => $domain->id])
                ->toArray()
        );

        /** @var View */
        return view('components.ontology.legal-domain-selector');
    }
}
