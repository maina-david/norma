<?php

namespace App\Models\Notify\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperNotificationHandoverUpdateCandidate
 */
class NotificationHandoverUpdateCandidate extends Pivot
{
    /** @var array<string, string> */
    protected $casts = [
        'meta' => 'array',
    ];
}
