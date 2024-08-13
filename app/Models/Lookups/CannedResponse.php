<?php

namespace App\Models\Lookups;

use App\Enums\Lookups\CannedResponseField;
use App\Http\ModelFilters\Lookups\CannedResponseFilter;
use App\Models\AbstractModel;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCannedResponse
 */
class CannedResponse extends AbstractModel implements Auditable
{
    use Filterable;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'librarian_canned_responses';

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

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeByType(Builder $builder, CannedResponseField $field): Builder
    {
        return $builder->where('for_field', $field->value);
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
        return $this->provideFilter(CannedResponseFilter::class);
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
            'response' => $this->response,
        ];
    }
}
