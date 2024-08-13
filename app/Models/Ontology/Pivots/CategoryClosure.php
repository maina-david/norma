<?php

namespace App\Models\Ontology\Pivots;

use App\Models\Traits\IsClosureTable;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryClosure
 */
class CategoryClosure extends Pivot
{
    use IsClosureTable;

    protected $table = 'corpus_category_closure';
}
