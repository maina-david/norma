<?php

namespace App\Models\Tasks;

use App\Contracts\UI\AsCalendarEvent;
use App\Enums\Tasks\TaskActivityType;
use App\Enums\Tasks\TaskRepeatInterval;
use App\Enums\Tasks\TaskStatus;
use App\Http\ModelFilters\Tasks\TaskFilter;
use App\Models\AbstractModel;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\Reminder;
use App\Models\Ontology\Category;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Pivots\FileTask;
use App\Models\Tasks\Pivots\TaskWatcher;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\AttachedToNormaAndOrganisation;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use IntlDateFormatter;
use OwenIt\Auditing\Contracts\Auditable;
use RRule\RRule;

/**
 * @property \App\Models\Assess\AssessmentItemResponse|null $assessmentItemResponse
 * @property \App\Models\Corpus\Reference|null              $reference
 * @property \App\Models\Storage\My\File|null               $taskableFile
 * @property \App\Models\Customer\Norma|null               $norma
 *
 * @mixin IdeHelperTask
 */
class Task extends AbstractModel implements AsCalendarEvent, Auditable
{
    use Filterable;
    use HasTitleLikeScope;
    use SoftDeletes;
    use HasComments;
    use EncodesHashId;
    use \OwenIt\Auditing\Auditable;
    use AttachedToNormaAndOrganisation;
    use ApiQueryFilterable;

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'due_on' => 'datetime',
        'completed_at' => 'datetime',
        'frequency_interval' => TaskRepeatInterval::class,
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
    public function actionArea(): BelongsTo
    {
        return $this->belongsTo(ActionArea::class);
    }

    /**
     * @return BelongsToMany
     */
    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, (new TaskWatcher())->getTable(), 'task_id', 'user_id')
            ->using(TaskWatcher::class);
    }

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(TaskProject::class, 'task_project_id');
    }

    /**
     * @return BelongsTo
     */
    public function norma(): BelongsTo
    {
        return $this->belongsTo(Norma::class, 'place_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Get all of the owning taskable models.
     *
     * @return MorphTo
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function assessmentItemResponse(): MorphTo
    {
        return $this->taskable();
    }

    /**
     * @return MorphTo
     */
    public function reference(): MorphTo
    {
        return $this->taskable();
    }

    /**
     * @return MorphTo
     */
    public function taskableFile(): MorphTo
    {
        return $this->taskable();
    }

    /**
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class, 'task_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatusActivity(): HasOne
    {
        return $this->hasOne(TaskActivity::class, 'task_id')
            ->ofMany(['id' => 'MAX'], fn ($query) => $query->where('activity_type', TaskActivityType::statusChange()->value));
    }

    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, (new FileTask())->getTable(), 'task_id', 'file_id')
            ->using(FileTask::class);
    }

    /**
     * @return MorphMany
     */
    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previous(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_task_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function next(): HasOne
    {
        return $this->hasOne(self::class, 'previous_task_id');
    }

    /**
     * @return BelongsTo
     */
    public function controlTypeCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'control_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function subjectCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subject_category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->whereRelation('norma', 'id', $norma->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereRelation('norma.organisation', 'id', $organisation->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccess(Builder $builder, Organisation $organisation, User $user): Builder
    {
        return $builder->whereRelation('norma', function ($q) use ($organisation, $user) {
            $q->active()->userHasAccess($user);
            $q->whereRelation('organisation', 'id', $organisation->id);
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('archived', false);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeExcludingDone(Builder $builder): Builder
    {
        return $builder->where('task_status', '!=', TaskStatus::done()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeOverdue(Builder $builder): Builder
    {
        return $builder->where(function ($q) {
            $q->whereNotNull('due_on');
            $q->where('due_on', '<', now()->yesterday()->endOfDay());
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeStatusIncomplete(Builder $builder): Builder
    {
        return $builder->where('task_status', '!=', TaskStatus::done()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeStatusDone(Builder $builder): Builder
    {
        return $builder->where('task_status', TaskStatus::done()->value);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * {@inheritDoc}
     */
    public function getCalendarEventDate(): ?\Carbon\Carbon
    {
        return $this->due_on;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalendarEventTarget(?string $route = null): string
    {
        return route($route ?? 'my.tasks.tasks.show', ['task' => $this->hash_id]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCalendarEventTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalendarEventID(): string|int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalendarEventBackground(): string
    {
        return match ($this->task_status) {
            TaskStatus::done()->value => 'bg-positive',
            TaskStatus::inProgress()->value => 'bg-warning-darker',
            default => 'bg-norma-gray-600'
        };
    }

    /**
     * {@inheritDoc}
     */
    public function isCalendarEventOverdue(): bool
    {
        return !TaskStatus::done()->is($this->task_status) && $this->due_on && now()->gt($this->due_on);
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(TaskFilter::class);
    }

    /**
     * User is only a watcher for a task.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isOnlyWatcher(User $user): bool
    {
        return $this->isWatcher($user) && !$this->isAuthor($user) && !$this->isAssignee($user);
    }

    /**
     * User is a watcher of a task.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isWatcher(User $user): bool
    {
        return $this->watchers->contains($user);
    }

    /**
     * User is the author of a task.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user): bool
    {
        return $user->is($this->author);
    }

    /**
     * User is the assignee of a task.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAssignee(User $user): bool
    {
        return $user->is($this->assignee);
    }

    /**
     * @return bool
     */
    public function isOverdue(): bool
    {
        $due = Carbon::parse($this->due_on);
        $now = Carbon::now('UTC');

        return $now > $due;
    }

    /**
     * Get the users that should be notified.
     *
     * @return Collection<int, User>
     */
    public function getNotifiables(): Collection
    {
        /** @var Collection<User> */
        $collection = $this->watchers;
        if ($this->author) {
            $collection->add($this->author);
        }
        if ($this->assignee) {
            $collection->add($this->assignee);
        }

        /** @var Collection<int, User> */
        return $collection->filter(function ($user) {
            /** @var User $user */
            return $user->isActive();
        });
    }

    /**
     * Get the translation key for the given user.
     *
     * @param User $user
     *
     * @return string
     */
    public function getNotificationSubjectTranslationKey(User $user): string
    {
        $status = $this->isOverdue() ? 'overdue' : 'due';

        if ($this->isAssignee($user)) {
            $prefix = TaskStatus::done()->is($this->task_status) ? '' : 'in';

            return "notifications.task_{$status}_{$prefix}complete_assignee_subject";
        }

        if ($this->isAuthor($user)) {
            return "notifications.task_{$status}_assigned_subject";
        }

        if ($this->isWatcher($user)) {
            return "notifications.task_{$status}_watching_subject";
        }

        return "notifications.task_{$status}_subject";
    }

    /**
     * Check if the task is cloned.
     *
     * @return bool
     */
    public function isCloned(): bool
    {
        return (bool) $this->source_task_id;
    }

    /**
     * @throws Exception
     *
     * @return string|null
     */
    public function getHumanReadableRRule(): ?string
    {
        if ($this->rrule) {
            $rrule = new RRule($this->rrule);

            return $rrule->humanReadable([
                'locale' => 'en_US',
                'date_format' => IntlDateFormatter::MEDIUM,
            ]);
        }

        return null;
    }
}
