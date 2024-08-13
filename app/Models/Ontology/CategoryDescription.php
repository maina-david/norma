<?php

namespace App\Models\Ontology;

use App\Models\AbstractModel;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCategoryDescription
 */
class CategoryDescription extends AbstractModel
{
    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
