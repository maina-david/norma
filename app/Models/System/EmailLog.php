<?php

namespace App\Models\System;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperEmailLog
 */
class EmailLog extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'delivered' => 'boolean',
        'opened' => 'boolean',
        'failed' => 'boolean',
    ];
}
