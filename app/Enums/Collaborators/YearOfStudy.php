<?php

namespace App\Enums\Collaborators;

use App\Enums\Traits\HasLabels;

enum YearOfStudy: string
{
    use HasLabels;

    case FIRST = '1st Year';
    case SECOND = '2nd Year';
    case THIRD = '3rd Year';
    case FOURTH = '4th Year';
    case POSTGRADUATE = 'Postgraduate';
    case PARALEGAL = 'Paralegal';
    case ADMITTED_LEGAL_PROFESSIONAL = 'Admitted Legal Professional';
    case OTHER = 'Other';

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'collaborators.collaborator.year_of_study_labels.';
    }
}
