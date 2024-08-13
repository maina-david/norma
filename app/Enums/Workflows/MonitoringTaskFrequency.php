<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum MonitoringTaskFrequency: int
{
    use HasLabels;

    case DAY = 1;
    case WEEK = 2;
    case MONTH = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.monitoring_task_frequency.';
    }
}
