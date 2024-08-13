<?php

namespace App\Models\Tasks\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperTaskWatcher
 */
class TaskWatcher extends Pivot
{
    /** @var string */
    protected $table = 'task_watchers';
}
