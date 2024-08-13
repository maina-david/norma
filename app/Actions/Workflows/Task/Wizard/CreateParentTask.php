<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Models\Workflows\Task;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateParentTask implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        return Task::create([
            'title' => $meta['document']->title,
            'document_id' => $meta['document']->id,
            'group_id' => $meta['groupId'],
            'project_type' => $meta['project_type'] ?? null,
            'board_id' => $meta['board']->id,
            'project_id' => $requestData['project'] ?? null,
            'task_type_id' => $meta['board']->parent_task_type_id,
            'auto_payment' => false,
            'requires_rating' => false,
        ]);
    }
}
