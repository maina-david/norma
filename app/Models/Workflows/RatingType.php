<?php

namespace App\Models\Workflows;

use App\Models\AbstractModel;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperRatingType
 */
class RatingType extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'librarian_rating_types';

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
