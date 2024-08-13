<?php

namespace App\Models\Ontology\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryContextQuestion
 */
class CategoryContextQuestion extends Pivot
{
    protected $table = 'corpus_category_context_question';
}
