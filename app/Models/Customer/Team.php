<?php

namespace App\Models\Customer;

use App\Http\ModelFilters\Customer\TeamFilter;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Pivots\NormaTeam;
use App\Models\Customer\Pivots\TeamUser;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperTeam
 */
class Team extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use Filterable;
    use HasTitleLikeScope;

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, (new TeamUser())->getTable())
            ->using(TeamUser::class);
    }

    /**
     * @return BelongsToMany
     */
    public function normas()
    {
        return $this->belongsToMany(Norma::class, (new NormaTeam())->getTable(), 'team_id', 'place_id')
            ->using(NormaTeam::class);
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
        return $this->provideFilter(TeamFilter::class);
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
