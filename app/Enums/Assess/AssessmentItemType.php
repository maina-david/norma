<?php

namespace App\Enums\Assess;

use App\Enums\Traits\HasLabels;

enum AssessmentItemType: int
{
    use HasLabels;
    case LEGACY = 1;
    case USER_DEFINED = 2;
    case COMPLIANCE = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'assess.assessment_item.type.';
    }
}
