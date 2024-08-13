<?php

namespace App\Enums\Arachno;

use App\Enums\Traits\HasLabels;

enum ConsolidationFrequency: int
{
    use HasLabels;

    case WEEKLY = 1;
    case MONTHLY = 2;
    case QUARTERLY = 3;
    case ANNUALLY = 4;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'arachno.source.consolidation_frequency.';
    }
}
