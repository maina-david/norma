<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoReference
 */
class LibryoReference extends Pivot
{
    /** @var string */
    protected $table = 'place_reference';
}
