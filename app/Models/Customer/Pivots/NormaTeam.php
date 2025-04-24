<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNormaTeam
 */
class NormaTeam extends Pivot
{
    protected $table = 'team_place';
}
