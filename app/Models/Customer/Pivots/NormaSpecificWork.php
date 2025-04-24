<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaSpecificWork
 */
class NormaSpecificWork extends Pivot
{
    protected $table = 'place_specific_work';
}
