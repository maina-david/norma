<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFileTask
 */
class FileTask extends Pivot
{
    protected $table = 'task_file';
}
