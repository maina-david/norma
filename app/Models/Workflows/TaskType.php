<?php

namespace App\Models\Workflows;

use App\Cache\Workflows\Collaborate\TaskTypeForRoleCache;
use App\Http\ModelFilters\Workflows\TaskTypeFilter;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Workflows\Pivots\BoardTaskType;
use App\Traits\HasCached;
use App\Traits\HasModelCache;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperTaskType
 */
class TaskType extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use Filterable;
    use HasCached;
    use HasModelCache;

    protected $table = 'librarian_task_types';

    /** @var array<string, string> */
    protected $casts = [
        'is_parent' => 'bool',
        'has_checks' => 'bool',
        'assignee_can_close' => 'bool',
        'validations' => 'array',
    ];

    /**
     * @return BelongsToMany
     */
    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, (new BoardTaskType())->getTable())
            ->using(BoardTaskType::class);
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function ratingGroup(): BelongsTo
    {
        return $this->belongsTo(RatingGroup::class);
    }

    /**
     * @return BelongsTo
     */
    public function taskRoute(): BelongsTo
    {
        return $this->belongsTo(TaskRoute::class);
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(TaskTypeFilter::class);
    }

    /**
     * Get the cache classes.
     *
     * @return array<int, class-string>
     */
    protected static function modelCaches(): array
    {
        return [
            TaskTypeForRoleCache::class,
        ];
    }

    /**
     * Get the permission string for the given task type.
     *
     * @param bool $withPrefix
     *
     * @return string
     */
    public function permission(bool $withPrefix): string
    {
        return ($withPrefix ? 'collaborate.' : '') . "workflows.access.computed_task_type_{$this->id}";
    }

    /**
     * Get the task types that the user can access.
     *
     * @return Collection
     */
    public static function accessible(): Collection
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            // @codeCoverageIgnoreStart
            return (new self())->newCollection();
            // @codeCoverageIgnoreEnd
        }

        return self::all()->filter(fn ($type) => $user->can($type->permission(true)));
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
            'name' => $this->name,
        ];
    }
}
