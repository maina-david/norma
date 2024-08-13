<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum WorkStatusOption: int
{
    use HasLabels;

    case NONE = 1;
    case NOT_PENDING = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task_type.work_status_options.';
    }
}
