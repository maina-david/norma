<?php

namespace App\Models\Customer\Pivots;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaRequirementsCollection
 */
class NormaRequirementsCollection extends Pivot
{
    protected $table = 'collection_place';

    public function norma(): BelongsTo
    {
        return $this->belongsTo(Norma::class, 'place_id');
    }

    public function requirementsCollection(): BelongsTo
    {
        return $this->belongsTo(RequirementsCollection::class, 'collection_id');
    }
}
