<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperDocKeyword
 */
class DocKeyword extends Pivot
{
    /** @var string */
    protected $table = 'doc_keyword';
}
