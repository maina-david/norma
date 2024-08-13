<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum ProjectSize: int
{
    use HasLabels;

    case SMALL = 0;
    case MEDIUM = 1;
    case LARGE = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.project.project_size_options.';
    }
}
