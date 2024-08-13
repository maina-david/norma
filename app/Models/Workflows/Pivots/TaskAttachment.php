<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperTaskAttachment
 */
class TaskAttachment extends Pivot
{
    protected $table = 'librarian_task_attachment';
}
