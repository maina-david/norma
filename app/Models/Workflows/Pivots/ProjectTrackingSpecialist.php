<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperProjectTrackingSpecialist
 */
class ProjectTrackingSpecialist extends Pivot
{
    /** @var string */
    protected $table = 'librarian_project_tracking_specialist';
}
