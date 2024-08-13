<?php

namespace App\Observers\Workflows;

use App\Actions\Workflows\Project\HandleProjectSaving;
use App\Models\Workflows\Project;

class ProjectObserver
{
    /**
     * @param Project $project
     *
     * @return void
     */
    public function saving(Project $project): void
    {
        HandleProjectSaving::run($project);
    }
}
