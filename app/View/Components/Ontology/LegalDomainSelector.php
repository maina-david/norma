<?php

namespace App\View\Components\Ontology;

use App\Models\Ontology\LegalDomain;
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LegalDomainSelector extends Component
{
    /** @var array<int|string,string> */
    public array $legalDomains;

    /**
     * Create a new component instance.
     *
     * @param string                                      $name
     * @param string                                      $label
     * @param array<int, string|int|null>|string|int|null $value
     * @param bool                                        $multiple
     * @param bool                                        $annotations
     * @param int|null                                    $locationId
     */
    public function __construct(
        public string $name,
        public string $label,
        public array|string|int|null $value = null,
        public bool $multiple = false,
        public bool $annotations = false,
        public ?int $locationId = null
    ) {
        $this->legalDomains = LegalDomain::active()
            ->forLocation($this->locationId)
            ->when($this->annotations, fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $this->legalDomains[''] = '-';

        //        $this->legalDomains = (new LegalDomainSelectorCache())->get();
        //
        //        if ($this->annotations) {
        //            $this->legalDomains = collect($this->legalDomains)->filter(fn ($dom) => !str_contains($dom, '|'))->toArray();
        //        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string|Factory
     */
    public function render()
    {
        return view('components.ontology.legal-domain-selector');
    }
}
