<?php

namespace App\Traits;

use Illuminate\View\ComponentAttributeBag;

trait UsesArchivedTableFilter
{
    /**
     * Get the archived filter.
     *
     * @return array<string, array<string, mixed>>
     */
    public function archivedTableFilter(): array
    {
        return [
            'archived' => [
                'label' => __('workflows.task.archived'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('archived'),
                        'name' => 'archived',
                        'type' => 'select',
                        'options' => ['' => '', 'Yes' => __('interface.yes'), 'No' => __('interface.no')],
                    ]),
                ]),
                'value' => fn ($value) => in_array($value, ['Yes', 'No']) ? $value : '',
            ],
        ];
    }
}
