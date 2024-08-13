<?php

namespace App\Enums\Collaborators;

use App\Enums\Traits\HasLabels;

enum CollaboratorStatus: int
{
    use HasLabels;

    case ACTIVE = 1;
    case INACTIVE = 0;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'collaborators.collaborator.status.';
    }
}
