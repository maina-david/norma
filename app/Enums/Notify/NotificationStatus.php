<?php

namespace App\Enums\Notify;

use App\Enums\Traits\HasLabels;

enum NotificationStatus: int
{
    use HasLabels;

    case NOT_REQUIRED = 1;
    case MAYBE_REQUIRED = 2;
    case REQUIRED = 3;
    case NOT_APPLICABLE = 4;
    case UNKNOWN = 5;
    case QUERY = 6;
    case REVIEWED = 7;
    case NOTIFICATION_ONLY = 8;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'corpus.doc.notification_status_enum.';
    }
}
