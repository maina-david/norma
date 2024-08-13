<?php

namespace App\Models\Auth;

use App\Models\AbstractModel;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperEmailReset
 */
class EmailReset extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
}
