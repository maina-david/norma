<?php

namespace App\Models\Customer\Pivots;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoRequirementsCollection
 */
class LibryoRequirementsCollection extends Pivot
{
    protected $table = 'collection_place';

    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class, 'place_id');
    }

    public function requirementsCollection(): BelongsTo
    {
        return $this->belongsTo(RequirementsCollection::class, 'collection_id');
    }
}
