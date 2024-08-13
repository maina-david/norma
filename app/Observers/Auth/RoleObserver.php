<?php

namespace App\Observers\Auth;

use App\Models\Auth\Role;

class RoleObserver
{
    /**
     * Listen to the creating event.
     *
     * @param Role $role
     */
    public function saving(Role $role): void
    {
        $permissions = $role->permissions ?? [];

        if (!in_array('access', $permissions)) {
            $permissions[] = 'access';
        }

        $role->permissions = $permissions;
    }
}
