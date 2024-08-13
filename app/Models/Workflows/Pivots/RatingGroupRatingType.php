<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperRatingGroupRatingType
 */
class RatingGroupRatingType extends Pivot
{
    protected $table = 'librarian_rating_group_rating_type';
}
