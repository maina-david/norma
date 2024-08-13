<?php

namespace App\Models\Notify\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalUpdateUser
 */
class LegalUpdateUser extends Pivot
{
    /** @var string */
    protected $table = 'register_notification_user';

    /** @var array<string, string> */
    protected $casts = [
        'read_status' => 'bool',
        'understood_status' => 'bool',
    ];
}
