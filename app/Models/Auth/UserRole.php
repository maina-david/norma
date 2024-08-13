<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperUserRole
 */
class UserRole extends Pivot
{
    protected $table = 'user_role';
    public $timestamps = false;
}
