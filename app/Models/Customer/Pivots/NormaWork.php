<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaWork
 */
class NormaWork extends Pivot
{
    /** @var string */
    protected $table = 'place_work';
}
