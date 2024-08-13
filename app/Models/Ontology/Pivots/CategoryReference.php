<?php

namespace App\Models\Ontology\Pivots;

use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryReference
 */
class CategoryReference extends Pivot
{
    protected $table = 'corpus_category_reference';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
