<?php

namespace App\Enums\Compilation;

use App\Enums\Traits\HasLabels;

enum ApplicabilityNoteType: int
{
    use HasLabels;

    case RELEVANT_TO_OPERATIONS = 1;
    case SEE_FOR_INTEREST = 2;
    case NOT_APPLICABLE_TO_OPERATIONS = 3;
    case NOT_APPLICABLE_TO_LOCATION = 4;
    case NOT_SUBSCRIBED_TO_CATEGORY = 5;
    case NOT_REQUIREMENT = 6;

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected static function languagePrefix(): string
    {
        return 'compilation.applicability.note_type.';
    }

    /**
     * Get the options for inclusion.
     *
     * @return \App\Enums\Compilation\ApplicabilityNoteType[]
     */
    public static function forInclusion(): array
    {
        return [
            self::RELEVANT_TO_OPERATIONS,
            self::SEE_FOR_INTEREST,
        ];
    }

    /**
     * Get the options for inclusion.
     *
     * @return \App\Enums\Compilation\ApplicabilityNoteType[]
     */
    public static function forExclusion(): array
    {
        return [
            self::NOT_APPLICABLE_TO_OPERATIONS,
            self::NOT_APPLICABLE_TO_LOCATION,
            self::NOT_SUBSCRIBED_TO_CATEGORY,
            self::NOT_REQUIREMENT,
        ];
    }
}
