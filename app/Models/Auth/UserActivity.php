<?php

namespace App\Models\Auth;

use App\Enums\Auth\UserActivityType;
use App\Models\AbstractModel;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperUserActivity
 */
class UserActivity extends AbstractModel
{
    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Gets the details json as an array. (Sadly it's a text field and not JSON).
     *
     * @return array<string, mixed>|null
     */
    public function getDetailsArrayAttribute(): ?array
    {
        return $this->details ? json_decode($this->details, true) : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     *
     * @return Builder
     */
    public function scopeTypeNormaActivate(Builder $builder): Builder
    {
        return $builder->where('activity_type', UserActivityType::normaActivate()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeSearchActivity(Builder $builder): Builder
    {
        return $builder->whereIn('activity_type', [
            UserActivityType::searchTerm()->value,
            UserActivityType::failedSearch()->value,
            UserActivityType::viewedTag()->value,
        ]);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeKnowYourLawActivity(Builder $builder): Builder
    {
        return $builder->whereIn('activity_type', [
            // UserActivityType::legalReportFilter()->value,
            UserActivityType::viewedFullText()->value,
            UserActivityType::viewedRequirements()->value,
            UserActivityType::viewedReference()->value,
            UserActivityType::viewedTabConsequences()->value,
            UserActivityType::viewedTabReadSummary()->value,
            UserActivityType::viewedTabSection()->value,
            UserActivityType::viewedTabSummary()->value,
        ]);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeComplianceActivity(Builder $builder): Builder
    {
        return $builder->whereIn('activity_type', [
            UserActivityType::normaActivate()->value,
            UserActivityType::normaComment()->value,
            UserActivityType::downloadedDocument()->value,
            UserActivityType::legalUpdateComment()->value,
            UserActivityType::referenceComment()->value,
            UserActivityType::viewedTabComments()->value,
            UserActivityType::viewedTabFiles()->value,
            UserActivityType::viewedTabReminders()->value,
        ]);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUpdateActivity(Builder $builder): Builder
    {
        return $builder->whereIn('activity_type', [
            UserActivityType::legalUpdateRead()->value,
            UserActivityType::legalUpdateUnderstood()->value,
        ]);
    }

    /**
     * Get all access platform activities in the month. As only one of these is created for a user
     * per day, it can be used to track the active days in a month.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeAccessedPlatformThisMonth(Builder $builder, User $user): Builder
    {
        return $builder->where('user_id', $user->id)
            ->where('activity_type', UserActivityType::accessedPlatform()->value)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30));
    }
}
