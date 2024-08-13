<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLocationReference
 */
class LocationReference extends Pivot
{
    /** @var string */
    protected $table = 'corpus_location_reference';
}
