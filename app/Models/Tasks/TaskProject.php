<?php

namespace App\Models\Tasks;

use App\Http\ModelFilters\Tasks\TaskProjectFilter;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperTaskProject
 */
class TaskProject extends AbstractModel implements Auditable
{
    use Filterable;
    use HasTitleLikeScope;
    use SoftDeletes;
    use EncodesHashId;
    use \OwenIt\Auditing\Auditable;

    /** @var array<string, string> */
    protected $casts = [
        'archived' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
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

    /**
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereRelation('organisation', 'id', $organisation->id);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('archived', false);
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
        return $this->provideFilter(TaskProjectFilter::class);
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
            'description' => $this->description,
        ];
    }
}
