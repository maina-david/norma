<?php

namespace App\Models\Workflows\Pivots;

use App\Models\Workflows\RatingType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperTaskRating
 */
class TaskRating extends Pivot
{
    protected $table = 'librarian_task_rating_type';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RatingType::class, 'rating_type_id');
    }
}
