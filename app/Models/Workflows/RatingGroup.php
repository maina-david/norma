<?php

namespace App\Models\Workflows;

use App\Models\AbstractModel;
use App\Models\Workflows\Pivots\RatingGroupRatingType;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperRatingGroup
 */
class RatingGroup extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'librarian_rating_groups';

    /**
     * @return BelongsToMany
     */
    public function ratingTypes(): BelongsToMany
    {
        return $this->belongsToMany(RatingType::class, (new RatingGroupRatingType())->getTable())
            ->using(RatingGroupRatingType::class);
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
