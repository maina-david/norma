<?php

namespace App\Models\Assess\Pivots;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessmentItemReferenceDraft
 */
class AssessmentItemReferenceDraft extends Pivot
{
    protected $table = 'assessment_item_reference_draft';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
