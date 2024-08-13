<?php

namespace App\Traits\Arachno;

use App\Models\Arachno\SourceCategory;
use Illuminate\View\ComponentAttributeBag;

trait UsesSourceCategoryFilter
{
    /**
     * @return array<string, mixed>
     */
    public function getSourceCategoryFilter(): array
    {
        return [
            'label' => __('arachno.source_category.source_category'),
            'render' => fn () => view('components.arachno.source-category.source-category-selector', [
                'value' => null,
                'attributes' => new ComponentAttributeBag(['@change' => '$dispatch(\'changed\', $el.value)']),
                'name' => 'source_categories',
                'label' => '',
                'required' => false,
                'allowEmpty' => true,
            ]),
            'multiple' => true,
            // @phpstan-ignore-next-line
            'value' => fn ($value) => SourceCategory::find($value)?->title ?? '',
        ];
    }
}
