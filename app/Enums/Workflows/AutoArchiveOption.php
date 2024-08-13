<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum AutoArchiveOption: int
{
    use HasLabels;

    case NONE = 1;
    case NO_PENDING_SUMMARIES = 4;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task_type.auto_archive_options.';
    }
}
