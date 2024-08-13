<?php

namespace App\Models\Assess\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessmentItemContextQuestion
 */
class AssessmentItemContextQuestion extends Pivot
{
    protected $table = 'corpus_context_question_assessment_item';
}
