<?php

namespace App\View\Components\Corpus\Reference;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CollaborateNav extends Component
{
    /** @var array<int, mixed> */
    public array $items = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $referenceId)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->items = [
            [
                'icon' => 'file-alt',
                'label' => __('corpus.reference.text'),
                'route' => 'collaborate.corpus.references.show',
                'routeParams' => ['reference' => $this->referenceId],
            ],
            [
                'icon' => 'balance-scale',
                'label' => __('ontology.legal_domain.index_title'),
                'route' => 'collaborate.corpus.references.legal-domains.index',
                'routeParams' => ['reference' => $this->referenceId],
                'permissions' => 'collaborate.corpus.reference.legal-domain.viewAny',
            ],
            [
                'icon' => 'check',
                'label' => __('assess.assessment_item.index_title'),
                // 'route' => '',
            ],
            [
                'icon' => 'hashtag',
                'label' => __('ontology.category.index_title'),
                // 'route' => '',
            ],
            [
                'icon' => 'question',
                'label' => __('compilation.context_question.index_title'),
                // 'route' => '',
            ],
            [
                'icon' => 'map-marker',
                'label' => __('geonames.location.index_title'),
                // 'route' => '',
            ],
            [
                'icon' => 'tags',
                'label' => __('ontology.tag.index_title'),
                // 'route' => '',
            ],
        ];

        /** @var View */
        return view('components.corpus.reference.collaborate-nav');
    }
}
