<?php

namespace App\Enums\Notify;

use App\Enums\Traits\HasLabels;

enum LegalUpdatePublishedStatus: int
{
    use HasLabels;

    case UNPUBLISHED = 0;
    case PUBLISHED = 1;
    case NOT_APPLICABLE = 2;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'notify.legal_update.published_status.';
    }
}
