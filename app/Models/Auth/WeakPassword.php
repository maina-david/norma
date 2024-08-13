<?php

namespace App\Models\Auth;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperWeakPassword
 */
class WeakPassword extends AbstractModel
{
    public $timestamps = false;
}
