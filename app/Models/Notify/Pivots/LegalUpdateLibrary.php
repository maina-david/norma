<?php

namespace App\Models\Notify\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalUpdateLibrary
 */
class LegalUpdateLibrary extends Pivot
{
    /** @var string */
    protected $table = 'register_notification_library';
}
