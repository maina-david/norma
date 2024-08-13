<?php

namespace App\Models\Corpus\Pivots;

use App\Models\Traits\IsClosureTable;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperReferenceClosure
 */
class ReferenceClosure extends Pivot
{
    use IsClosureTable;

    protected $table = 'reference_closure';
}
