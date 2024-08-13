<?php

namespace App\Models\Corpus\Pivots;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLocationReferenceDraft
 */
class LocationReferenceDraft extends Pivot
{
    /** @var string */
    protected $table = 'location_reference_draft';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
