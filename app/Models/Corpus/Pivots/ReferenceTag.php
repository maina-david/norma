<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperReferenceTag
 */
class ReferenceTag extends Pivot
{
    /** @var string */
    protected $table = 'corpus_reference_tag';
}
