<?php

namespace App\Models\Ontology\Pivots;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryReferenceDraft
 */
class CategoryReferenceDraft extends Pivot
{
    protected $table = 'category_reference_draft';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
