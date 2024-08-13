<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFileLocation
 */
class FileLocation extends Pivot
{
    protected $table = 'file_location';
}
