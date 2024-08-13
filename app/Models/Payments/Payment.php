<?php

namespace App\Models\Payments;

use App\Http\ModelFilters\Payments\PaymentFilter;
use App\Models\AbstractModel;
use App\Models\Collaborators\Team;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperPayment
 */
class Payment extends AbstractModel implements Auditable
{
    use Filterable;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'librarian_payments';

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
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * @return HasMany
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class, 'payment_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePaidFrom(Builder $builder, Carbon $date): Builder
    {
        return $builder->where('created_at', '>=', $date);
    }

    public function scopePaidTo(Builder $builder, Carbon $date): Builder
    {
        return $builder->where('created_at', '<=', $date);
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
        return $this->provideFilter(PaymentFilter::class);
    }
}
