<?php

namespace App\Models\Compilation\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperContextQuestionRequirementsCollection
 */
class ContextQuestionRequirementsCollection extends Pivot
{
    protected $table = 'corpus_context_question_location';
}
