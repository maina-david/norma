<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperTeamUser
 */
class TeamUser extends Pivot
{
    protected $table = 'team_user';
}
