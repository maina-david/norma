<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum TaskTypeOption: int
{
    use HasLabels;
    case COMPLETE = 1;
    case APPLIED = 2;
    case NONE = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task_type.options.';
    }
}
