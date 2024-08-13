<?php

namespace App\Traits\Arachno;

use App\Models\Arachno\Source;
use Illuminate\View\ComponentAttributeBag;

trait UsesSourceFilter
{
    /**
     * @param bool $multiple
     *
     * @return array<string, mixed>
     */
    public function getSourceFilter(bool $multiple = false): array
    {
        $onChange = $multiple ? '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (e) { return e.value }))'
            : '$dispatch(\'changed\', $el.value)';

        return [
            'label' => __('arachno.source.source'),
            'render' => fn () => view('components.arachno.source.source-selector', [
                'value' => null,
                'attributes' => new ComponentAttributeBag(['@change' => $onChange]),
                'name' => 'source',
                'label' => '',
                'required' => false,
                'allowEmpty' => true,
                'multiple' => $multiple,
            ]),
            // @phpstan-ignore-next-line
            'value' => fn ($value) => Source::find($value)?->title ?? '',
            'multiple' => $multiple,
        ];
    }
}
