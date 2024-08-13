<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperReferenceReference
 */
class ReferenceReference extends Pivot
{
    /** @var string */
    protected $table = 'corpus_reference_reference';
}
