<?php

namespace App\Enums\Assess;

use App\Enums\Traits\HasLabels;

enum ReassessInterval: int
{
    use HasLabels;

    case DAY = 1;
    case WEEK = 2;
    case MONTH = 3;
    case YEAR = 4;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'assess.assessment_item.reassess_interval.';
    }
}
