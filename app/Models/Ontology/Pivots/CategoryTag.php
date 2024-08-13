<?php

namespace App\Models\Ontology\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryTag
 */
class CategoryTag extends Pivot
{
    /** @var string */
    protected $table = 'corpus_category_tag';
}
