<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum YesNoPending: int
{
    use HasLabels;

    case NO = 0;
    case YES = 1;
    case PENDING = 2;
    case COMPLETED = 4;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.common.yes_no_pending.';
    }
}
