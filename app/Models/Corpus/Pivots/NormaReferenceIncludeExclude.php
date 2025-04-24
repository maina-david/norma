<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaReferenceIncludeExclude
 */
class NormaReferenceIncludeExclude extends Pivot
{
    /** @var string */
    protected $table = 'place_reference_include_exclude';

    /** @var bool */
    public $timestamps = false;
}
