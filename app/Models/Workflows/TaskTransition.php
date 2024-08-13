<?php

namespace App\Models\Workflows;

use App\Enums\Application\ApplicationType;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTaskTransition
 */
class TaskTransition extends AbstractModel
{
    protected $table = 'librarian_task_transitions';

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::admin()->value => ['create', 'delete', 'update'],
            ApplicationType::collaborate()->value => ['create', 'delete', 'update'],
            ApplicationType::my()->value => ['create', 'delete', 'update'],
        ];
    }
}
