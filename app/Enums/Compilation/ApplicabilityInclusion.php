<?php

namespace App\Enums\Compilation;

use App\Enums\Traits\HasLabels;

enum ApplicabilityInclusion: int
{
    use HasLabels;
    case INCLUDED_IN_STREAM = 1;
    case EXCLUDED_FROM_STREAM = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'compilation.context_question.applicability_inclusion.';
    }
}
