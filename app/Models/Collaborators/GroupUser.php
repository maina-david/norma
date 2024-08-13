<?php

namespace App\Models\Collaborators;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperGroupUser
 */
class GroupUser extends Pivot
{
    protected $table = 'librarian_group_user';
}
