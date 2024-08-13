<?php

namespace App\Observers\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Events\Workflows\TaskAssigneeChanged;
use App\Events\Workflows\TaskComplexityChanged;
use App\Events\Workflows\TaskDocumentChanged;
use App\Events\Workflows\TaskManagerChanged;
use App\Events\Workflows\TaskStatusChanged;
use App\Events\Workflows\TaskTypeChanged;
use App\Models\Workflows\Task;
use App\Observers\Observer;
use App\Services\Workflows\TaskActionManager;
use App\Traits\Workflows\CreatesTransitions;
use Illuminate\Database\Eloquent\Model;

class TaskObserver extends Observer
{
    use CreatesTransitions;

    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they have been changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onSavedEvents(): array
    {
        return [
            'user_id' => TaskAssigneeChanged::class,
            'manager_id' => TaskManagerChanged::class,
        ];
    }

    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they have been changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onUpdatedEvents(): array
    {
        return [
            'complexity' => TaskComplexityChanged::class,
            'document_id' => TaskDocumentChanged::class,
            'task_status' => TaskStatusChanged::class,
            'task_type_id' => TaskTypeChanged::class,
        ];
    }

    /**
     * Listen to the saved event.
     *
     * @param Task $model
     *
     * @return void
     */
    public function saved(Model $model): void
    {
        if (!($model->isDirty('task_status') && TaskStatus::archive()->is($model->task_status))) {
            TaskActionManager::validateAndTrigger($model);
        }

        if (!$model->isParent() && $model->isDirty('task_status') && TaskStatus::done()->is($model->task_status)) {
            $this->updateParentTask($model);
        }

        parent::saved($model);
    }

    /**
     * Listen to the creating event.
     *
     * @param Task $task
     *
     * @return void
     */
    public function creating(Task $task): void
    {
        if (auth()->id()) {
            $task->update(['author_id' => auth()->id()]);
        }
    }

    /**
     * Listen to the created event.
     *
     * @param Task $task
     *
     * @return void
     */
    public function created(Task $task): void
    {
        $this->createTransition($task, 'task_id');
    }

    /**
     * Listen to the deleted event.
     *
     * @param Task $task
     *
     * @return void
     */
    public function deleted(Task $task): void
    {
        if ($task->isParent()) {
            $task->childTasks()->delete();
        }
    }

    /**
     * Update the parent task status.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Workflows\Task $task
     *
     * @return void
     */
    protected function updateParentTask(Task $task): void
    {
        $task->load(['parent']);

        if ($task->parent && !TaskStatus::archive()->is($task->parent->task_status)) {
            $complete = Task::where('parent_task_id', $task->parent->id)
                ->whereIn('task_status', [
                    TaskStatus::pending()->value,
                    TaskStatus::todo()->value,
                    TaskStatus::inProgress()->value,
                    TaskStatus::inReview()->value,
                ])
                ->doesntExist();

            $task->parent->update(['task_status' => $complete ? TaskStatus::done()->value : $task->parent->task_status]);
        }
    }
}
