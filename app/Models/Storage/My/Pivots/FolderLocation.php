<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFolderLocation
 */
class FolderLocation extends Pivot
{
    /** @var string */
    protected $table = 'folder_location';
}
