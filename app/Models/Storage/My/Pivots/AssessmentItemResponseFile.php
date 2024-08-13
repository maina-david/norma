<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperAssessmentItemResponseFile
 */
class AssessmentItemResponseFile extends Pivot
{
    protected $table = 'assessment_item_response_file';
}
