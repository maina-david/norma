<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum ProjectType: int
{
    use HasLabels;
    case NEW_PROJECT = 1;
    case MAINTENANCE = 2;

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task.project_type.';
    }
}
