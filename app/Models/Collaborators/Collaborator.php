<?php

namespace App\Models\Collaborators;

use App\Enums\Workflows\TaskStatus;
use App\Http\ModelFilters\Collaborators\CollaboratorFilter;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Auth\UserRole;
use App\Models\Traits\CollaborateNotifiable;
use App\Models\Traits\EncodesHashId;
use App\Models\Workflows\Pivots\TaskRating;
use App\Models\Workflows\Task;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property Profile $profile
 *
 * @mixin IdeHelperCollaborator
 */
class Collaborator extends User
{
    use CollaborateNotifiable;
    use Filterable;
    use EncodesHashId;

    /** @var string */
    protected $table = 'users';

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('collaborateUser', function (Builder $builder) {
            $builder->has('profile');
            //            $builder->where('user_type', UserType::collaborate());
        });
    }

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
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, (new GroupUser())->getTable(), 'user_id')
            ->using(GroupUser::class);
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id')
            ->using(UserRole::class);
    }

    /**
     * @return HasMany
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(TaskRating::class, 'user_id');
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function ratingAverage(): HasMany
    {
        return $this->ratings()
            ->selectRaw('rating_type_id, user_id, avg(score) as score')
            ->groupBy('rating_type_id');
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function completedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id')
            ->where('task_status', TaskStatus::done())
            ->whereNotNull('parent_task_id');
    }

    /**
     * @return HasMany
     */
    public function todoTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id')
            ->where('task_status', TaskStatus::todo())
            ->whereNotNull('parent_task_id');
    }

    /**
     * @return HasMany
     */
    public function inProgressTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id')
            ->where('task_status', TaskStatus::inProgress())
            ->whereNotNull('parent_task_id');
    }

    /**
     * @return HasMany
     */
    public function inReviewTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id')
            ->where('task_status', TaskStatus::inReview())
            ->whereNotNull('parent_task_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get the overall rating of the user.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeWithScore(Builder $builder): Builder
    {
        $user = (new self())->getTable();

        return $builder->addSelect([
            'score' => TaskRating::whereColumn("{$user}.id", 'user_id')->selectRaw('avg(score)'),
        ])->withCasts(['score' => 'float']);
    }

    /**
     * Get the hours the collaborator has worked in the week.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeWithWeekHours(Builder $builder): Builder
    {
        $user = (new self())->getTable();

        return $builder->addSelect([
            'week_hours' => Task::whereColumn("{$user}.id", 'user_id')
                ->whereHas('transitions', function ($query) {
                    $query->where('created_at', '>=', now()->startOfDay()->startOfWeek())
                        ->where('transitioned_field', 'task_status')
                        ->whereIn('task_status', [
                            TaskStatus::todo()->value, TaskStatus::done()->value, TaskStatus::inReview()->value,
                        ]);
                })
                ->selectRaw('sum(units)'),
        ])->withCasts(['week_hours' => 'float']);
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
        return $this->provideFilter(CollaboratorFilter::class);
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
            'fname' => $this->fname,
            'sname' => $this->sname,
            'email' => $this->email,
        ];
    }
}
