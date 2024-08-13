<?php

namespace App\Models\Ontology;

use App\Http\ModelFilters\Ontology\UserTagFilter;
use App\Models\AbstractModel;
use App\Models\Customer\Organisation;
use App\Models\Traits\HasFulltextSearchScope;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperUserTag
 */
class UserTag extends AbstractModel
{
    use SoftDeletes;
    use Filterable;
    use HasFulltextSearchScope;
    use HasTitleLikeScope;

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param int     $organisationId
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('organisation', (new Organisation())->getKeyName(), '=', $organisationId);
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
        return $this->provideFilter(UserTagFilter::class);
    }
}
