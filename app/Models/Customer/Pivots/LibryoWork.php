<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoWork
 */
class LibryoWork extends Pivot
{
    /** @var string */
    protected $table = 'place_work';
}
