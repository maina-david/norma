<?php

namespace App\Models\Workflows;

use App\Enums\Application\ApplicationType;
use App\Models\AbstractModel;
use App\Models\Collaborators\Collaborator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTaskCheck
 */
class TaskCheck extends AbstractModel
{
    protected $table = 'librarian_task_checks';

    protected $primaryKey = 'task_id';

    public $incrementing = false;

    /**
     * @return BelongsTo
     */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::collaborate()->value => ['update', 'delete', 'view'],
        ];
    }
}
