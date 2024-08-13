<?php

namespace App\Enums\Compilation;

use App\Enums\Traits\HasLabels;

enum ContextQuestionActivityType: int
{
    use HasLabels;

    case ANSWER_CHANGED = 1;
    case COMMENT = 2;

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected static function languagePrefix(): string
    {
        return 'compilation.context_question.activity_type.';
    }
}
