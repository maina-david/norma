<?php

namespace App\Models\Assess\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessmentItemCategory
 */
class AssessmentItemCategory extends Pivot
{
    /** @var string */
    protected $table = 'corpus_assessment_item_category';
}
