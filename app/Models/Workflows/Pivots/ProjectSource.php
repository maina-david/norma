<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperProjectSource
 */
class ProjectSource extends Pivot
{
    /** @var string */
    protected $table = 'librarian_project_source';
}
