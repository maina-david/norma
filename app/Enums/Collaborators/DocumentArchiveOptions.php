<?php

namespace App\Enums\Collaborators;

use App\Enums\Traits\HasLabels;

enum DocumentArchiveOptions: int
{
    use HasLabels;

    case EXPIRED = 1;
    case NEW_REQUIREMENTS = 2;
    case NOT_REQUIRED = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'collaborators.collaborator.document_archive_options.';
    }
}
