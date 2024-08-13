<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperReferenceRequirementsCollection
 */
class ReferenceRequirementsCollection extends Pivot
{
    /** @var string */
    protected $table = 'corpus_location_reference';
}
