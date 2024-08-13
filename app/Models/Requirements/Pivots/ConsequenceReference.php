<?php

namespace App\Models\Requirements\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperConsequenceReference
 */
class ConsequenceReference extends Pivot
{
    /** @var string */
    protected $table = 'corpus_consequence_reference';
}
