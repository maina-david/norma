<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoSpecificWork
 */
class LibryoSpecificWork extends Pivot
{
    protected $table = 'place_specific_work';
}
