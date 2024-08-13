<?php

namespace App\Enums\Corpus;

use App\Enums\Traits\HasLabels;

enum UpdateCandidateActionStatus: int
{
    use HasLabels;

    case PENDING = 1;
    case DONE = 2;
    case REVIEW = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'corpus.doc.update_candidate.status.';
    }
}
