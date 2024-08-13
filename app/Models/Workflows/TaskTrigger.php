<?php

namespace App\Models\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\AbstractModel;
use App\Services\Workflows\TaskTriggerManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTaskTrigger
 */
class TaskTrigger extends AbstractModel
{
    protected $table = 'librarian_task_triggers';

    /** @var array<string, string> */
    protected $casts = [
        'triggered' => 'bool',
        'details' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */
    /**
     * @return BelongsTo
     */
    public function sourceTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'source_task_id');
    }

    /**
     * @return BelongsTo
     */
    public function targetTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'target_task_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */
    public function trigger(): void
    {
        app(TaskTriggerManager::class)->trigger($this);
    }

    public function shouldTrigger(TaskTransition $transition): bool
    {
        if ($this->triggered) {
            return false;
        }
        switch ($this->trigger_type) {
            case TaskTriggerType::ON_DONE->value:
                return (bool) ($transition->task_status === TaskStatus::done()->value
                    && $transition->transitioned_field === 'task_status'
                );
            case TaskTriggerType::ON_STATUS_CHANGE->value:
                /** @var Task */
                $sourceTask = (new Task())->newQueryWithoutScopes()->find($this->source_task_id);

                return (bool) ($transition->transitioned_field === 'task_status'
                    && $this->details && $this->details['task_status'] == $sourceTask->task_status
                );

            default:
                return false;
        }
    }
}
