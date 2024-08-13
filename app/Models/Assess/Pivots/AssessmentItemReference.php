<?php

namespace App\Models\Assess\Pivots;

use App\Models\Assess\AssessmentItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessmentItemReference
 */
class AssessmentItemReference extends Pivot
{
    protected $table = 'corpus_assessment_item_reference';

    /**
     * @return BelongsTo
     */
    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class);
    }
}
