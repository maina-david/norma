<?php

namespace App\Models\Assess;

use App\Enums\Assess\ReassessInterval;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Http\ModelFilters\Assess\AssessmentItemResponseFilter;
use App\Models\AbstractModel;
use App\Models\Assess\Pivots\AssessSnapshotAssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Pivots\AssessmentItemResponseFile;
use App\Models\Tasks\Task;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasComments;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\Customer\Norma|null                                                                                        $norma
 * @property \App\Models\Storage\My\Pivots\AssessmentItemResponseFile|\App\Models\Assess\Pivots\AssessSnapshotAssessmentItemResponse $pivot
 * @property \App\Models\Assess\AssessmentItem                                                                                       $assessmentItem
 *
 * @mixin IdeHelperAssessmentItemResponse
 */
class AssessmentItemResponse extends AbstractModel
{
    use HasComments;
    use SoftDeletes;
    use Filterable;
    use EncodesHashId;

    /** @var array<string, string> */
    protected $casts = [
        'frequency_interval' => ReassessInterval::class,
        'next_due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */
    /**
     * @return BelongsTo
     */
    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class)->withTrashed();
    }

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
    public function lastAnsweredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_answered_by');
    }

    /**
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AssessmentActivity::class, 'assessment_item_response_id');
    }

    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, (new AssessmentItemResponseFile())->getTable(), 'assessment_item_response_id', 'file_id')
            ->using(AssessmentItemResponseFile::class);
    }

    /**
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * @return HasOne
     */
    // public function latestAnsweredActivity(): HasOne
    // {
    //     return $this->hasOne(AssessmentActivity::class, 'assessment_item_response_id')
    //         ->ofMany(['id' => 'max'], function ($q) {
    //             $q->statusChange();
    //         });
    // }

    /**
     * @return BelongsToMany
     */
    public function assessSnapshots(): BelongsToMany
    {
        return $this->belongsToMany(AssessSnapshot::class, (new AssessSnapshotAssessmentItemResponse())->getTable(), 'assessment_item_response_id', 'assess_snapshot_id')
            ->using(AssessSnapshotAssessmentItemResponse::class)
            ->withPivot(['answer', 'risk_rating']);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotDraft(Builder $builder): Builder
    {
        return $builder->where('answer', '!=', ResponseStatus::draft()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDraft(Builder $builder): Builder
    {
        return $builder->where('answer', ResponseStatus::draft()->value);
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->whereRelation('norma', 'id', $norma->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereRelation('norma', function ($q) use ($organisation) {
            $q->whereRelation('organisation', 'id', $organisation->id);
        });
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccess(Builder $builder, Organisation $organisation, User $user): Builder
    {
        return $builder->whereRelation('norma', function ($q) use ($organisation, $user) {
            $q->active()->userHasAccess($user);
            $q->whereRelation('organisation', 'id', $organisation->id);
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeAnsweredNo(Builder $builder): Builder
    {
        return $builder->where('answer', ResponseStatus::no()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeAnsweredYes(Builder $builder): Builder
    {
        return $builder->where('answer', ResponseStatus::yes()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotAssessed(Builder $builder): Builder
    {
        return $builder->where('answer', ResponseStatus::notAssessed()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotApplicable(Builder $builder): Builder
    {
        return $builder->where('answer', ResponseStatus::notApplicable()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNonCompliant(Builder $builder): Builder
    {
        return $builder->answeredNo();
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForHighRisk(Builder $builder): Builder
    {
        return $builder->whereHas('assessmentItem', function ($q) {
            $q->riskHigh();
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForMediumRisk(Builder $builder): Builder
    {
        return $builder->whereHas('assessmentItem', function ($q) {
            $q->riskMedium();
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForLowRisk(Builder $builder): Builder
    {
        return $builder->whereHas('assessmentItem', function ($q) {
            $q->riskLow();
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForNotRatedRisk(Builder $builder): Builder
    {
        return $builder->whereHas('assessmentItem', function ($q) {
            $q->riskNotRated();
        });
    }

    /**
     * @param Builder $builder
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearchAssessmentItem(Builder $builder, string $search): Builder
    {
        return $builder->whereHas('assessmentItem', function ($q) use ($search) {
            $q->searchComponents($search);
        });
    }

    /**
     * @param Builder    $builder
     * @param array<int> $domainIds
     *
     * @return Builder
     */
    public function scopeForLegalDomains(Builder $builder, array $domainIds): Builder
    {
        return $builder->whereRelation('assessmentItem', function ($q) use ($domainIds) {
            $q->forLegalDomains($domainIds);
        });
    }

    /**
     * @param Builder        $builder
     * @param AssessmentItem $assessmentItem
     *
     * @return Builder
     */
    public function scopeForAssessmentItem(Builder $builder, AssessmentItem $assessmentItem): Builder
    {
        return $builder->whereRelation('assessmentItem', 'id', $assessmentItem->id);
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
        return $this->provideFilter(AssessmentItemResponseFilter::class);
    }

    /**
     * We might allow users to update a risk rating for assessment items.
     * We'll probably use the assessment Item responses table for that.. But this is a central
     * place to get a risk rating for a response so we only need to change it here when implemented.
     *
     * @return RiskRating
     */
    public function getRiskRating(): RiskRating
    {
        /** @var RiskRating */
        return RiskRating::fromValue($this->assessmentItem->risk_rating);
    }
}
