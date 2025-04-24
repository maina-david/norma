<?php

namespace App\Models\Assess;

use App\Models\AbstractModel;
use App\Models\Assess\Pivots\AssessSnapshotAssessmentItemResponse;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperAssessSnapshot
 */
class AssessSnapshot extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'month_date' => 'datetime',
        'last_answered' => 'datetime',
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
    public function norma(): BelongsTo
    {
        return $this->belongsTo(Norma::class, 'place_id');
    }

    /**
     * @return BelongsToMany
     */
    public function assessmentItemResponses(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentItemResponse::class, (new AssessSnapshotAssessmentItemResponse())->getTable(), 'assess_snapshot_id', 'assessment_item_response_id')
            ->using(AssessSnapshotAssessmentItemResponse::class)
            ->withPivot(['answer', 'risk_rating']);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $query
     * @param Carbon  $monthEnd
     *
     * @return Builder
     */
    public function scopeForMonth(Builder $query, Carbon $monthEnd): Builder
    {
        return $query->where('month_date', $monthEnd->endOfMonth());
    }
    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */
}
