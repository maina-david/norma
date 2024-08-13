<?php

namespace App\Models\Compilation\Pivots;

use App\Models\Traits\IsClosureTable;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperRequirementCollectionClosure
 */
class RequirementCollectionClosure extends Pivot
{
    use IsClosureTable;

    protected $table = 'corpus_location_closure';
}
