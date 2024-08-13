<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum DependentUnitsOption: int
{
    use HasLabels;

    case IGNORE = 1;
    case OWN_VALUE = 2;
    case MULTIPLE_OF = 3;
    case CUSTOM_VALUE = 4;
    case SUMMARIES = 5;
    case STATEMENTS = 6;
    case SUMMARIES_AND_STATEMENTS = 7;
    case NO_ASSESS_IN_STATEMENTS_3MIN = 8;
    case NO_ASSESS_IN_STATEMENTS_4MIN = 9;
    case REFERENCES = 10;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task.dependent_units_options.';
    }

    /**
     * Get the options that require a multiplier.
     *
     * @return array<array-key, string>
     */
    public static function withMultiple(): array
    {
        return [
            (string) self::CUSTOM_VALUE->value,
            (string) self::MULTIPLE_OF->value,
            (string) self::REFERENCES->value,
        ];
    }
}
