<?php

namespace App\Models\Assess;

use App\Models\AbstractModel;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperAssessmentItemDescription
 */
class AssessmentItemDescription extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_assessment_item_descriptions';

    /**
     * @return BelongsTo
     */
    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class, 'assessment_item_id');
    }

    /**
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
