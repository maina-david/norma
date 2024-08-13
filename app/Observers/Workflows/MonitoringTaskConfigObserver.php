<?php

namespace App\Observers\Workflows;

use App\Models\Workflows\MonitoringTaskConfig;

class MonitoringTaskConfigObserver
{
    /**
     * Listen to the creating event.
     *
     * @param \App\Models\Workflows\MonitoringTaskConfig $config
     *
     * @return void
     */
    public function creating(MonitoringTaskConfig $config): void
    {
        $config->tasks = $config->tasks ?? [];
    }
}
