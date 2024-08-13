<?php

namespace App\Models\Workflows;

use App\Enums\Application\ApplicationType;
use App\Enums\Workflows\TaskComplexity;
use App\Enums\Workflows\TaskStatus;
use App\Http\ModelFilters\Workflows\TaskFilter;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\TeamRate;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Payments\PaymentRequest;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Workflows\Pivots\TaskAttachment;
use App\Models\Workflows\Pivots\TaskRating;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin IdeHelperTask
 */
class Task extends AbstractModel
{
    use SoftDeletes;
    use Filterable;

    protected $table = 'librarian_tasks';

    /** @var array<int, string> */
    protected $with = [
        'taskType',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'auto_payment' => 'bool',
        'requires_rating' => 'bool',
        'start_date' => 'date',
        'due_date' => 'datetime',
    ];

    /**
     * Get the expected earnings.
     *
     * @return string|null
     */
    public function getApproximateEarningsAttribute(): ?string
    {
        /** @var Collaborator|null $user */
        $user = Collaborator::with(['profile.team.rates' => fn ($builder) => $builder->where('for_task_type_id', $this->task_type_id)])
            ->whereKey(Auth::id())
            ->first();

        if (!$user || !$this->units || !$user->profile->is_team_admin) {
            return null;
        }

        /** @var TeamRate|null $rate */
        $rate = $user->profile->team->rates->first();

        return $rate ? sprintf('%s %s', $user->profile->team->currency, number_format($rate->rate_per_unit * $this->units, 2)) : null;
    }

    /**
     * Get the value in minutes of the complexity level.
     *
     * @return string
     */
    public function getComplexityMinutesAttribute(): string
    {
        if ($this->complexity) {
            $method = "level{$this->complexity}";

            return TaskComplexity::{$method}();
        }

        return '';
    }

    /**
     * Get the label for the task complexity.
     *
     * @return string
     */
    public function getComplexityLabelAttribute(): string
    {
        if ($this->complexity) {
            $method = "level{$this->complexity}";

            return TaskComplexity::{$method}()->label();
        }

        return '';
    }

    /**
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(TaskApplication::class);
    }

    /**
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    /**
     * @return HasMany
     */
    public function checks(): HasMany
    {
        return $this->hasMany(TaskCheck::class);
    }

    /**
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'author_id');
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo
     */
    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    /**
     * @return BelongsTo
     */
    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(self::class, 'depends_on_task_id');
    }

    /**
     * @return HasMany
     */
    public function dependedBy(): HasMany
    {
        return $this->hasMany(self::class, 'depends_on_task_id');
    }

    /**
     * @return BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return HasMany
     */
    public function childTasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    /**
     * @return HasMany
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(TaskRating::class);
    }

    /**
     * @return BelongsToMany
     */
    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Attachment::class, (new TaskAttachment())->getTable())
            ->using(TaskAttachment::class);
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(TaskTransition::class);
    }

    /**
     * @return HasMany
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    /**
     * @return HasMany
     */
    public function outstandingPaymentRequests(): HasMany
    {
        return $this->paymentRequests()->where('paid', false);
    }

    /**
     * @return HasMany
     */
    public function paidPaymentRequests(): HasMany
    {
        return $this->paymentRequests()->where('paid', true);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(TaskTrigger::class, 'source_task_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for getting user activities.
     *
     * @param Builder $builder
     * @param int     $userId
     *
     * @return Builder
     */
    public function scopeForActivity(Builder $builder, int $userId): Builder
    {
        return $builder->where('user_id', $userId)
            ->where('task_status', TaskStatus::done()->value)
            ->with([
                'ratings.type',
                'ratings' => fn ($builder) => $builder->where('user_id', $userId),
            ])
            ->addSelect([
                'completed_at' => TaskTransition::whereColumn('task_id', $this->getTable() . '.id')
                    ->where('user_id', $userId)
                    ->where('task_status', TaskStatus::done()->value)
                    ->where('transitioned_field', 'task_status')
                    ->select('created_at')
                    ->take(1)
                    ->orderBy('id', 'desc'),
            ])
            ->withCasts([
                'completed_at' => 'date',
            ])
            ->orderBy('completed_at', 'desc');
    }

    /**
     * @param Builder               $builder
     * @param array<array-key, int> $jurisdictions
     *
     * @return Builder
     */
    public function scopeForJurisdictions(Builder $builder, array $jurisdictions): Builder
    {
        $locations = Location::select(['id'])
            ->whereKey($jurisdictions)
            ->orWhereHas('ancestors', fn ($query) => $query->whereKey($jurisdictions));

        $filter = fn ($query) => $query->whereIn('primary_location_id', $locations);

        return $builder->where(function ($query) use ($locations, $filter) {
            $query->whereHas('document.work', $filter)
                ->orWhereHas('document.legalUpdate', $filter)
                ->orWhereHas('document.locations', fn ($builder) => $builder->whereIn('id', $locations));
        });
    }

    /**
     * @param Builder               $builder
     * @param array<array-key, int> $domains
     *
     * @return Builder
     */
    public function scopeForLegalDomain(Builder $builder, array $domains): Builder
    {
        $filter = fn ($query) => $query->whereIn('id', $domains);

        return $builder->where(function ($query) use ($filter) {
            $query->whereHas('document.work.references.legalDomains', $filter)
                ->orWhereHas('document.legalUpdate.legalDomains', $filter)
                ->orWhereHas('document.legalDomains', $filter);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(TaskFilter::class);
    }

    /**
     * Check if the assignee can close the task.
     *
     * @return bool
     */
    public function assigneeCanClose(): bool
    {
        return $this->taskType?->assignee_can_close && $this->isRated();
    }

    /**
     * Check if the current user is the assignee.
     *
     * @return bool
     */
    public function isAssignee(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && $user->id === $this->user_id;
    }

    /**
     * Check if the task is a parent task.
     *
     * @return bool
     */
    public function isParent(): bool
    {
        if ($this->relationLoaded('childTasks')) {
            return $this->childTasks->count() > 0;
        }

        return $this->childTasks()->count() > 0;
    }

    /**
     * Check if the task requires rating and is rated.
     *
     * @return bool
     */
    public function isRated(): bool
    {
        if (!$this->requires_rating) {
            return true;
        }

        if ($this->relationLoaded('ratings')) {
            return $this->ratings->count() > 0;
        }

        return $this->ratings()->count() > 0;
    }

    /**
     * Check if the task can be closed.
     *
     * @return bool
     */
    public function canClose(): bool
    {
        // should autopay and has no units.
        if ($this->auto_payment && is_null($this->units)) {
            return false;
        }

        /** @var User|null $user */
        $user = Auth::user();

        // no logged in user so definately cannot close.
        if (!$user) {
            return false;
        }

        if (!$this->isAssignee() && $user->can('collaborate.workflows.task.override-status')) {
            return true;
        }

        // check if the assigned user can close the task.
        if ($this->user_id === $user->id) {
            return $this->assigneeCanClose();
        }

        // if it's a parent task, check if user can close it.
        if ($this->isParent()) {
            return $user->can('collaborate.workflows.task.close-parent');
        }

        // If the user can close any open task
        if ($user->can('collaborate.workflows.task.close')) {
            return true;
        }

        // if the user is assigned a task that depends on this task and they have the permission to close.
        $assignedToDependant = $this->dependedBy()->where('user_id', $user->id)->exists();

        return $assignedToDependant && $user->can('collaborate.workflows.task.close-depended-on');
    }

    /**
     * Check if the user can rate the task.
     *
     * @return bool
     */
    public function canRate(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($this->user_id && !$this->requires_rating || $this->isRated() || !$user || $user->id === $this->user_id) {
            return false;
        }

        if ($user->can('collaborate.workflows.task.override-rating')) {
            return true;
        }

        return $user->can('collaborate.workflows.task.rate') && $this->isAssigneeOnDependant();
    }

    /**
     * The auth user is assigned to a task that depends on this.
     *
     * @return bool
     */
    public function isAssigneeOnDependant(): bool
    {
        // if the user is assigned a task that depends on this task.
        return $this->dependedBy()->where('user_id', Auth::id())->exists();
    }

    /**
     * Check if the user can change the task status to the given one.
     *
     * @param TaskStatus $status
     *
     * @return bool
     */
    public function canChangeStatusTo(TaskStatus $status): bool
    {
        return collect($this->availableStatuses())
            ->filter(fn ($available) => $status->is($available))
            ->isNotEmpty();
    }

    /**
     * Check if the task is archived or done.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return in_array($this->task_status, [TaskStatus::done()->value, TaskStatus::archive()->value]);
    }

    /**
     * Get the available statuses.
     *
     * @return array<array-key, TaskStatus>
     */
    public function availableStatuses(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $statuses = [];

        // can set any status as long as they are not assigned the task
        $override = !$this->isAssignee() && $user->can('collaborate.workflows.task.override-status');
        // not assigned and can set to pending
        $setAsPending = !$this->isAssignee() && $user->can('collaborate.workflows.task.set-pending');
        // not assigned and you can re-open
        $reopen = $this->isClosed() && !$this->isAssignee() && $user->can('collaborate.workflows.task.re-open');

        $isPending = TaskStatus::pending()->is($this->task_status);

        if ($override || $isPending || ($setAsPending && $reopen)) {
            $statuses[] = TaskStatus::pending();
        }

        $siblingIsArchivedAndIsPending = false;

        if ($user->can('collaborate.workflows.task.set-todo-when-previous-is-archived') && $isPending) {
            $this->load(['board']);
            $order = explode(',', $this->board?->task_type_order ?? '');
            $index = array_search($this->task_type_id, $order);
            $siblingIsArchivedAndIsPending = Task::where('parent_task_id', $this->parent_task_id)
                ->where('task_type_id', $order[$index - 1] ?? 0)
                ->where('task_status', TaskStatus::archive()->value)
                ->exists();
        }

        $isTodo = TaskStatus::todo()->is($this->task_status);
        $isInProgress = TaskStatus::inProgress()->is($this->task_status);

        if ($override || $isTodo || $reopen || ($isInProgress && $this->isAssignee()) || $siblingIsArchivedAndIsPending) {
            $statuses[] = TaskStatus::todo();
        }

        $todoToInProgress = TaskStatus::todo()->is($this->task_status) && $this->isAssignee();
        $reviewToInProgress = TaskStatus::inReview()->is($this->task_status) && $this->isAssigneeOnDependant() && $user->can('collaborate.workflows.task.reverse-to-in-progress');

        if ($override || $isInProgress || $reopen || $todoToInProgress || $reviewToInProgress) {
            $statuses[] = TaskStatus::inProgress();
        }

        $isReview = TaskStatus::inReview()->is($this->task_status);
        $inProgressToReview = TaskStatus::inProgress()->is($this->task_status) && $this->isAssignee();

        if ($override || $isReview || $reopen || $inProgressToReview) {
            $statuses[] = TaskStatus::inReview();
        }

        $isDone = TaskStatus::done()->is($this->task_status);
        $inProgressToDone = TaskStatus::inProgress()->is($this->task_status) && $this->canClose();
        $reviewToDone = TaskStatus::inReview()->is($this->task_status) && $this->canClose();

        if ($override || $isDone || $reviewToDone || $inProgressToDone) {
            $statuses[] = TaskStatus::done();
        }

        $isArchive = TaskStatus::archive()->is($this->task_status);
        $canArchive = (!$this->isAssignee() && $user->can('collaborate.workflows.task.archive')
            || $this->isAssignee() && $user->can('collaborate.workflows.task.archive-own-task'));

        if ($override || $isArchive || $canArchive) {
            $statuses[] = TaskStatus::archive();
        }

        return $statuses;
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::collaborate()->value => ['view'],
        ];
    }

    /**
     * Update the parent task status. Derived from the children status.
     *
     * @return void
     */
    public function updateParentTaskStatus(): void
    {
        if ($this->isParent()) {
            return;
        }

        $statuses = Task::where('parent_task_id', $this->parent_task_id)
            ->where('task_status', '!=', TaskStatus::archive()->value)
            ->pluck('task_status')
            ->unique();

        if ($statuses->count() > 1) {
            Task::whereKey($this->parent_task_id)->update(['task_status' => TaskStatus::inProgress()->value]);

            return;
        }

        if (TaskStatus::done()->is($statuses->first())) {
            Task::whereKey($this->parent_task_id)->update(['task_status' => TaskStatus::done()->value]);
        }
    }

    /**
     * Get the task siblings that the user can access.
     *
     * @param User                            $user
     * @param WorkExpression|LegalUpdate|null $related
     * @param Task|null                       $task
     *
     * @return \Illuminate\Support\Collection<int, Task>
     */
    public static function siblings(User $user, WorkExpression|LegalUpdate|null $related, ?Task $task): \Illuminate\Support\Collection
    {
        $query = Task::with(['taskType.taskRoute', 'document']);

        $query->when($task, function ($builder) use ($task) {
            $builder->where(function ($query) use ($task) {
                /** @var Task $task */
                $query->where('parent_task_id', $task->parent_task_id ?? $task->id)
                    ->orWhere('id', $task->parent_task_id ?? $task->id);
            });
        });

        $query->when(!$task && $related instanceof WorkExpression, function ($builder) use ($related) {
            $builder->whereRelation('document', 'work_expression_id', '=', $related?->id);
        });

        $query->when(!$task && $related instanceof LegalUpdate, function ($builder) use ($related) {
            $builder->whereRelation('document', 'legal_update_id', '=', $related?->id);
        });

        /** @var Collection<Task> $tasks */
        $tasks = $query->get();

        /** @var \Illuminate\Support\Collection<int, Task> */
        return $tasks
            ->filter(function ($item) use ($user) {
                /** @var Task $item */
                return $user->can('view', $item);
            })
            ->values();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
