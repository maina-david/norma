<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperBoardTaskType
 */
class BoardTaskType extends Pivot
{
    protected $table = 'librarian_board_task_type';
}
