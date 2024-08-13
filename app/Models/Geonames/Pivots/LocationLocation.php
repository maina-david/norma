<?php

namespace App\Models\Geonames\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLocationLocation
 */
class LocationLocation extends Pivot
{
    /** @var string */
    protected $table = 'corpus_location_closure';

    /** @var bool */
    public $timestamps = false;
}
