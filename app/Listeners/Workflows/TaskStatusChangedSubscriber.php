<?php

namespace App\Listeners\Workflows;

use App\Enums\Corpus\ReferenceType;
use App\Enums\Corpus\WorkStatus;
use App\Enums\Workflows\AutoArchiveOption;
use App\Enums\Workflows\OnToDoValidationType;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskValidationType;
use App\Events\Workflows\TaskStatusChanged;
use App\Jobs\Corpus\CacheWorkContent;
use App\Jobs\Corpus\GenerateReferenceContentExtracts;
use App\Mail\Workflows\TaskFeedbackEmail;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use App\Notifications\Workflows\TaskStatusChangedNotification;
use App\Traits\Workflows\CreatesTransitions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskStatusChangedSubscriber
{
    use CreatesTransitions;

    /**
     * Listen to the assignee changed event.
     *
     * @param TaskStatusChanged $event
     *
     * @return void
     */
    public function handle(TaskStatusChanged $event): void
    {
        $this->notifyAssignee($event->task);

        $this->createTransition($event->task, 'task_status');

        $this->publishRelatedWork($event->task);

        $this->generateReferenceExtracts($event->task);

        $this->cacheWorkContent($event->task);

        $this->handleNoSummaryArchive($event->task);

        $this->handleArchive($event->task);

        $event->task->updateParentTaskStatus();
    }

    /**
     * Notify the assignee of the assignment.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function notifyAssignee(Task $task): void
    {
        // no mail since it's archiving
        if (TaskStatus::archive()->is($task->task_status)) {
            return;
        }

        $task->load('assignee');

        // if it's not done then we can send the standard mail.
        if (!TaskStatus::done()->is($task->task_status) && $task->user_id != Auth::id()) {
            $task->assignee?->notify(new TaskStatusChangedNotification($task));

            return;
        }

        // if it was in review, and now it's done and the user that triggered the status change is not this user
        if ($task->assignee && TaskStatus::inReview()->is($task->getOriginal('task_status')) && $task->user_id != Auth::id()) {
            Mail::to($task->assignee)->send(new TaskFeedbackEmail($task));
        }
    }

    /**
     * Cache the related work.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function cacheWorkContent(Task $task): void
    {
        $task->load(['board', 'document.work']);

        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? [];

        $shouldTrigger = $defaults['cache_related_work'] ?? false;

        $shouldTrigger = $shouldTrigger && ($task->task_status == ($defaults['cache_related_work_trigger'] ?? false));

        if ($shouldTrigger && $task->document?->work) {
            CacheWorkContent::dispatch($task->document->work);
        }
    }

    /**
     * Publish the related work.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function publishRelatedWork(Task $task): void
    {
        $task->load(['board', 'document.work']);

        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? [];

        $shouldTrigger = $defaults['publish_related_work'] ?? false;

        $shouldTrigger = $shouldTrigger && ($task->task_status == ($defaults['publish_related_work_trigger'] ?? false));

        if ($shouldTrigger && $task->document?->work) {
            $task->document->work->update(['status' => WorkStatus::active()->value]);
        }
    }

    /**
     * Publish the related work.
     *
     * @param Task $task
     *
     * @return void
     */
    protected function generateReferenceExtracts(Task $task): void
    {
        $task->load(['board', 'document.work']);

        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? [];

        $shouldTrigger = $defaults['generate_reference_extracts'] ?? false;

        $shouldTrigger = $shouldTrigger && ($task->task_status == ($defaults['generate_reference_extracts_trigger'] ?? false));

        if ($shouldTrigger && $task->document?->work) {
            dispatch(new GenerateReferenceContentExtracts($task->document->work->id));
        }
    }

    /**
     * Archive children tasks that are not done.
     *
     * @param Task $task
     *
     * @return void
     */
    private function handleArchive(Task $task): void
    {
        if (!TaskStatus::archive()->is($task->task_status) || !$task->isParent()) {
            return;
        }

        Task::where('parent_task_id', $task->id)
            ->whereIn('task_status', [TaskStatus::pending()->value, TaskStatus::todo()->value])
            ->get()
            ->each(fn ($item) => $item->update(['task_status' => TaskStatus::archive()->value]));
    }

    /**
     * Archive the task when there is no summary and the task type permits.
     *
     * @param Task $task
     *
     * @return void
     */
    private function handleNoSummaryArchive(Task $task): void
    {
        if (!TaskStatus::todo()->is($task->task_status)) {
            return;
        }

        $task->load(['document', 'board']);

        $validations = $task->board->task_type_defaults[$task->task_type_id]['validations'] ?? [];

        $validations = $validations[TaskValidationType::TODO->value][OnToDoValidationType::AUTO_ARCHIVE->value] ?? null;

        if (!$validations || AutoArchiveOption::tryFrom($validations) !== AutoArchiveOption::NO_PENDING_SUMMARIES) {
            return;
        }

        $workQuery = WorkExpression::select(['work_id'])
            ->where('id', $task->document?->work_expression_id);

        $hasPending = Reference::where('type', ReferenceType::citation()->value)
            ->whereIn('work_id', $workQuery)
            ->has('summaryDraft')
            ->exists();

        if ($hasPending) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $task->task_status = TaskStatus::archive()->value;
        $task->save();

        Task::where('depends_on_task_id', $task->id)
            ->where('task_status', TaskStatus::pending()->value)
            ->get()
            ->each(function ($dependant) {
                /** @var Task $dependant */
                $dependant->task_status = TaskStatus::todo()->value;
                $dependant->save();
            });
    }
}
