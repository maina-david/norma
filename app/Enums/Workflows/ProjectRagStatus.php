<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum ProjectRagStatus: int
{
    use HasLabels;

    case GREEN = 0;
    case AMBER = 1;
    case RED = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.project.project_rag_status_options.';
    }
}
