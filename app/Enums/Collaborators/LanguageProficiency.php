<?php

namespace App\Enums\Collaborators;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static LanguageProficiency beginner()
 * @method static LanguageProficiency intermediate()
 * @method static LanguageProficiency advanced()
 * @method static LanguageProficiency native()
 */
class LanguageProficiency extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'collaborators.language_proficiency.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'native' => 4,
        ];
    }
}
