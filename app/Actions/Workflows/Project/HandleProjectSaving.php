<?php

namespace App\Actions\Workflows\Project;

use App\Models\Workflows\Project;
use App\Services\HTMLPurifierService;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleProjectSaving
{
    use AsAction;

    public function __construct(protected HTMLPurifierService $htmlPurifierService)
    {
    }

    public function handle(Project $project): void
    {
        $project->description = $project->description ? $this->htmlPurifierService->cleanTaskDescription($project->description) : null;
    }
}
