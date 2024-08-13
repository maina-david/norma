<?php

namespace App\Models\Collaborators;

use App\Http\ModelFilters\Collaborators\GroupFilter;
use App\Models\AbstractModel;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperGroup
 */
class Group extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use Filterable;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'librarian_groups';

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(GroupFilter::class);
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
