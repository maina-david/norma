<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoUser
 */
class LibryoUser extends Pivot
{
    protected $table = 'place_user';
}
