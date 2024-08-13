<?php

namespace App\Enums\Compilation;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static self no()
 * @method static self yes()
 * @method static self maybe()
 */
class ContextQuestionAnswer extends Enum
{
    protected static string $langPrefix = 'compilation.context_question.answers.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, int>
     */
    protected static function enums(): array
    {
        return [
            'no' => 0,
            'yes' => 1,
            'maybe' => 2,
        ];
    }
}
