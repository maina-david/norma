<?php

namespace App\Traits;

use Illuminate\View\ComponentAttributeBag;

trait UsesDateRangeFilters
{
    /**
     * @return array<string, mixed>
     */
    public function getToFilter(): array
    {
        return [
            'label' => __('timestamps.to'),
            'render' => fn () => view('components.ui.input', [
                'value' => $this->getFilterValue('to'),
                'attributes' => new ComponentAttributeBag(['@change' => '$dispatch(\'changed\', $el.value)']),
                'type' => 'date',
                'name' => 'to',
                'label' => '',
                'required' => false,
                'selectName' => null,
            ]),
            'value' => fn ($value) => $value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFromFilter(): array
    {
        return [
            'label' => __('timestamps.from'),
            'render' => fn () => view('components.ui.input', [
                'value' => $this->getFilterValue('from'),
                'attributes' => new ComponentAttributeBag(['@change' => '$dispatch(\'changed\', $el.value)']),
                'type' => 'date',
                'name' => 'from',
                'label' => '',
                'required' => false,
                'selectName' => null,
            ]),
            'value' => fn ($value) => $value,
        ];
    }
}
