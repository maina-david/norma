<?php

namespace App\Enums\Compilation;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static self includeExcludeCitation()
 * @method static self locationChangeed()
 * @method static self contextQuestionAnswered()
 * @method static self legalDomainChangeed()
 * @method static self manualRecompilation()
 * @method static self requirementsUpdated()
 */
class RecompilationActivityType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, int>
     */
    protected static function enums(): array
    {
        return [
            'includeExcludeCitation' => 1,
            'locationChangeed' => 2,
            'contextQuestionAnswered' => 3,
            'legalDomainChangeed' => 4,
            'manualRecompilation' => 5,
            'requirementsUpdated' => 6,
        ];
    }
}
