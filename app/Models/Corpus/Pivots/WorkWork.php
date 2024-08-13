<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperWorkWork
 */
class WorkWork extends Pivot
{
    /** @var string */
    protected $table = 'corpus_work_work';
}
