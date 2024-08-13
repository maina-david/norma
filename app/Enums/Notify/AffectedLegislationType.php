<?php

namespace App\Enums\Notify;

use App\Enums\Traits\HasLabels;

enum AffectedLegislationType: int
{
    use HasLabels;

    case AMENDED = 1;
    case NEW = 2;
    case REPEALED = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'corpus.doc.affected_legislation_type_enum.';
    }
}
