<?php

namespace App\Enums\Compilation;

use App\Enums\Traits\HasLabels;

enum ApplicabilityActivityType: int
{
    use HasLabels;

    case ANSWER_CHANGED = 1;
    case COMMENT = 2;
    case REQUIREMENT_REMOVED = 3;
    case REQUIREMENT_ADDED = 4;

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected static function languagePrefix(): string
    {
        return 'compilation.applicability.activity_type.';
    }
}
