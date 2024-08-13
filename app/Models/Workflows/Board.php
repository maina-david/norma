<?php

namespace App\Models\Workflows;

use App\Cache\Workflows\Collaborate\BoardSelectorCache;
use App\Models\AbstractModel;
use App\Models\Workflows\Pivots\BoardTaskType;
use App\Traits\HasCached;
use App\Traits\HasModelCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperBoard
 */
class Board extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasCached;
    use HasModelCache;

    protected $table = 'librarian_boards';

    /** @var array<string, string> */
    protected $casts = [
        'source_document_required' => 'boolean',
        'task_type_defaults' => 'array',
        'wizard_steps' => 'array',
        'optional_wizard_steps' => 'array',
    ];

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForUpdate(Builder $builder): Builder
    {
        return $builder->where('for_legal_update', true);
    }

    /**
     * @return BelongsTo
     */
    public function parentTaskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'parent_task_type_id');
    }

    /**
     * @return BelongsToMany
     */
    public function taskTypes(): BelongsToMany
    {
        return $this->belongsToMany(TaskType::class, (new BoardTaskType())->getTable())
            ->using(BoardTaskType::class);
    }

    /**
     * Get the cache classes.
     *
     * @return array<int, class-string>
     */
    protected static function modelCaches(): array
    {
        return [
            BoardSelectorCache::class,
        ];
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
        ];
    }
}
