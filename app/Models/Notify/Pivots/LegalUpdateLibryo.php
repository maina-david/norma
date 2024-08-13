<?php

namespace App\Models\Notify\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalUpdateLibryo
 */
class LegalUpdateLibryo extends Pivot
{
    /** @var string */
    protected $table = 'register_notification_place';
}
