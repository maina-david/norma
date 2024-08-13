<?php

namespace App\Traits\Workflows;

use App\Models\Workflows\TaskType;
use Illuminate\View\ComponentAttributeBag;

trait UsesTaskTypeFilter
{
    /**
     * @return array<string, mixed>
     */
    public function getTaskTypeFilter(): array
    {
        return [
            'label' => __('workflows.task.task_type'),
            'multiple' => true,
            'render' => fn () => view('components.workflows.task-type.task-type-selector', [
                'filters' => ['is_parent' => false],
                'label' => '',
                'multiple' => true,
                'value' => $this->getFilterValue('types'),
                'attributes' => new ComponentAttributeBag([]),
            ]),
            'value' => fn ($value) => TaskType::cached($value)?->name ?? '',
        ];
    }
}
