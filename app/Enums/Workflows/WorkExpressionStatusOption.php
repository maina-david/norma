<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum WorkExpressionStatusOption: int
{
    use HasLabels;

    case NONE = 1;
    case ACTIVATED = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task_type.work_expression_status_options.';
    }
}
