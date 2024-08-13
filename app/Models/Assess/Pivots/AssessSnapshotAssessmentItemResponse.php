<?php

namespace App\Models\Assess\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessSnapshotAssessmentItemResponse
 */
class AssessSnapshotAssessmentItemResponse extends Pivot
{
    protected $table = 'assess_snapshot_response';
}
