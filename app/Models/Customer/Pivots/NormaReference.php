<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaReference
 */
class NormaReference extends Pivot
{
    /** @var string */
    protected $table = 'place_reference';
}
