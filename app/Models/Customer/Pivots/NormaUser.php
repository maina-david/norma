<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaUser
 */
class NormaUser extends Pivot
{
    protected $table = 'place_user';
}
