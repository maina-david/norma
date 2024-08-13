<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFileReference
 */
class FileReference extends Pivot
{
    /** @var string */
    protected $table = 'register_item_file';
}
