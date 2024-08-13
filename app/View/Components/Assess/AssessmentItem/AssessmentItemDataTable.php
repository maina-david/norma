<?php

namespace App\View\Components\Assess\AssessmentItem;

use App\Models\Ontology\LegalDomain;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\View\Components\Ui\DataTable;

class AssessmentItemDataTable extends DataTable
{
    use UsesBookmarksTableFilter;

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'description_full' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item.description-link', [
                    'assessmentItem' => $row,
                ]),
                'heading' => '',
            ],
            'description_with_domain' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item.description-with-domains', [
                    'assessmentItem' => $row,
                ]),
                'heading' => '',
            ],
            'description' => [
                'render' => fn ($row) => $row->toDescription(),
                'heading' => '',
                // 'classes' => 'py-3 px-4',
            ],
            'applicable_streams' => [
                'render' => fn ($row) => $row->assessment_responses_count,
                'heading' => __('assess.assessment_item.applicable_streams_no'),
                'align' => 'center',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'domains' => [
                'label' => __('assess.category'),
                'render' => fn () => view('partials.ontology.my.legal-domain.render-assess-category-selector'),
                // @phpstan-ignore-next-line
                'value' => fn ($value) => LegalDomain::find($value)->title,
                'multiple' => true,
            ],
            ...$this->bookmarksFilter(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function actions(): array
    {
        return [
            'add_unused_items' => [
                'label' => __('assess.assessment_item.add_unused_items'),
            ],
        ];
    }
}
