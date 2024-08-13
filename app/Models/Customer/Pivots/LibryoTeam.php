<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibryoTeam
 */
class LibryoTeam extends Pivot
{
    protected $table = 'team_place';
}
