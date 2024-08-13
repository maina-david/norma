<?php

namespace App\Enums\Compilation;

use App\Enums\Traits\HasLabels;

enum ApplicabilityRecommendation: int
{
    use HasLabels;
    case RECOMMENDED = 1;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'compilation.context_question.applicability_recommendation.';
    }
}
