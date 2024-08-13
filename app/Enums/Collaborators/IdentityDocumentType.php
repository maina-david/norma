<?php

namespace App\Enums\Collaborators;

use App\Enums\Traits\HasLabels;

enum IdentityDocumentType: string
{
    use HasLabels;

    case DRIVERS_LICENSE = 'Drivers License';
    case ID = 'National ID';
    case PASSPORT = 'Passport';

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'collaborators.identity_type.';
    }
}
