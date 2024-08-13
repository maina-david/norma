<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum TaskStartDay: int
{
    use HasLabels;
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task_start_date.';
    }
}
