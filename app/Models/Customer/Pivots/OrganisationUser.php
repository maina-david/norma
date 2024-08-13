<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperOrganisationUser
 */
class OrganisationUser extends Pivot
{
    /** @var array<string, string> */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
