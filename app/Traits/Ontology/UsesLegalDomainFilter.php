<?php

namespace App\Traits\Ontology;

use App\Models\Ontology\LegalDomain;
use Illuminate\View\ComponentAttributeBag;

trait UsesLegalDomainFilter
{
    /**
     * @return array<string, mixed>
     */
    public function getLegalDomainFilter(): array
    {
        return [
            'label' => __('ontology.legal_domain.index_title'),
            'render' => fn () => view('components.ontology.legal-domain.selector', [
                'value' => $this->getFilterValue('domain'),
                'name' => 'domain',
                'label' => '',
                'attributes' => new ComponentAttributeBag([
                    'annotations' => true,
                ]),
            ]),
            'multiple' => true,
            'value' => fn ($value) => $value === 'all'
                // @codeCoverageIgnoreStart
                ? __('ontology.legal_domain.all')
                // @codeCoverageIgnoreEnd
                : LegalDomain::cached($value)?->title ?? '',
        ];
    }
}
