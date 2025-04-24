<?php

namespace App\Models\Notify;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperReminder
 */
class Reminder extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /** @var array<string, string> */
    protected $casts = [
        'notification_config' => 'array',
        'reminded' => 'bool',
        'completed_at' => 'datetime',
        'remind_on' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function norma(): BelongsTo
    {
        return $this->belongsTo(Norma::class, 'place_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return MorphTo
     */
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */
    /**
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeCreatedBy(Builder $builder, User $user): Builder
    {
        return $builder->whereRelation('author', 'id', $user->id);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotReminded(Builder $builder): Builder
    {
        return $builder->where('reminded', false);
    }
}
