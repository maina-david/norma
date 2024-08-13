<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use Lorisleiva\Actions\Concerns\AsAction;

class SelectProjectType implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): int
    {
        return $requestData['project_type'];
    }
}
