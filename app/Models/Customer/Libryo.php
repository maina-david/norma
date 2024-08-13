<?php

namespace App\Models\Customer;

use App\Enums\System\LibryoModule;
use App\Http\ModelFilters\Customer\LibryoFilter;
use App\Models\AbstractModel;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Library;
use App\Models\Compilation\RecompilationActivity;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Pivots\CompiledLibryoReference;
use App\Models\Customer\Pivots\CompiledLibryoWork;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
use App\Models\Customer\Pivots\LegalDomainLibryo;
use App\Models\Customer\Pivots\LibryoReference;
use App\Models\Customer\Pivots\LibryoRequirementsCollection;
use App\Models\Customer\Pivots\LibryoTeam;
use App\Models\Customer\Pivots\LibryoUser;
use App\Models\Customer\Pivots\LibryoWork;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateLibryo;
use App\Models\Ontology\LegalDomain;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasTitleLikeScope;
use App\Services\Assess\AssessStatsService;
use App\Traits\HasSettings;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property CompilationSetting $compilationSetting
 *
 * @mixin IdeHelperLibryo
 */
class Libryo extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use HasSettings;
    use Filterable;
    use HasTitleLikeScope;
    use HasComments;
    use ApiQueryFilterable;
    use EncodesHashId;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'places';

    /** @var array<string, string> */
    protected $casts = [
        'settings' => 'array',
        'needs_recompilation' => 'bool',
        'compilation_in_progress' => 'bool',
        'deactivated' => 'bool',
        'auto_compiled' => 'bool',
        'compiled_at' => 'datetime',
    ];

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

    /**
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, (new LibryoTeam())->getTable(), 'place_id')
            ->using(LibryoTeam::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, (new LibryoUser())->getTable(), 'place_id')
            ->using(LibryoUser::class);
    }

    /**
     * @return BelongsTo
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new LegalDomainLibryo())->getTable(), 'place_id', 'legal_domain_id')
            ->using(LegalDomainLibryo::class);
    }

    /**
     * @return BelongsToMany
     */
    public function legalUpdates(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->belongsToMany(LegalUpdate::class, (new LegalUpdateLibryo())->getTable(), 'place_id', 'register_notification_id')
            ->using(LegalUpdateLibryo::class)
            ->orderBy((new LegalUpdate())->qualifyColumn('created_at'), 'DESC');
    }

    /**
     * @return HasMany
     */
    // public function assessmentActivities(): HasMany
    // {
    //     return $this->hasMany(AssessmentActivity::class, 'place_id');
    // }

    /**
     * @return BelongsToMany
     */
    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new ContextQuestionLibryo())->getTable(), 'place_id', 'context_question_id')
            ->using(ContextQuestionLibryo::class)
            ->withPivot(['answer', 'last_answered_by']);
    }

    /**
     * @return HasMany
     */
    public function contextQuestionAnswers(): HasMany
    {
        return $this->hasMany(ContextQuestionLibryo::class, 'place_id');
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new LibryoReference())->getTable(), 'place_id', 'reference_id')
            ->using(LibryoReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function compiledReferences(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new CompiledLibryoReference())->getTable(), 'place_id', 'reference_id')
            ->using(CompiledLibryoReference::class);
    }

    /**
     * @return HasMany
     */
    public function assessmentItemResponses(): HasMany
    {
        return $this->hasMany(AssessmentItemResponse::class, 'place_id');
    }

    /**
     * @return BelongsToMany
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class, (new LibryoWork())->getTable(), 'place_id', 'work_id')
            ->using(LibryoWork::class);
    }

    /**
     * @return BelongsToMany
     */
    public function compiledWorks(): BelongsToMany
    {
        return $this->belongsToMany(Work::class, (new CompiledLibryoWork())->getTable(), 'place_id', 'work_id')
            ->using(CompiledLibryoWork::class);
    }

    public function recompilationActivities(): HasMany
    {
        return $this->hasMany(RecompilationActivity::class, 'place_id');
    }

    public function compilationSetting(): HasOne
    {
        return $this->hasOne(CompilationSetting::class, 'place_id');
    }

    /**
     * Remove this relation once we stop using the location_id for compilation.
     *
     * @return BelongsTo
     */
    public function requirementsCollection(): BelongsTo
    {
        return $this->belongsTo(RequirementsCollection::class, 'location_id');
    }

    public function requirementsCollections(): BelongsToMany
    {
        return $this->belongsToMany(RequirementsCollection::class, (new LibryoRequirementsCollection())->getTable(), 'place_id', 'collection_id')
            ->using(LibryoRequirementsCollection::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * libryo streams that the given user has access to.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeUserHasAccess(Builder $builder, User $user): Builder
    {
        // the place_user table is used as a cache - access is determined by the teams the user is a part of,
        // and that is cached in the place_user table with the via_teams set to true
        return $builder->whereHas('users', fn ($q) => $q->whereKey($user->id));
        // return $builder->where(function ($query) use ($user) {
        //     $filter = function ($q) use ($user) {
        //         $q->where('id', $user->id);
        //     };
        //     $query->whereHas('teams.users', $filter)->orWhereHas('users', $filter);
        // });
    }

    /**
     * libryo streams that the given user has access to.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeUserHasAccessViaTeams(Builder $builder, User $user): Builder
    {
        return $builder->whereHas('teams.users', fn ($q) => $q->whereKey($user->id));
    }

    /**
     * Scope a query to only include active libryos.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deactivated', false);
    }

    /**
     * Scope a query to only include inactive libryos.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeInActive(Builder $query): Builder
    {
        return $query->where('deactivated', true);
    }

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

    /**
     * @param Builder $builder
     * @param int     $teamId
     *
     * @return Builder
     */
    public function scopeForTeam(Builder $builder, int $teamId): Builder
    {
        return $builder->whereRelation('teams', (new Team())->getKeyName(), '=', $teamId);
    }

    /**
     * @param Builder     $builder
     * @param LegalUpdate $update
     *
     * @return Builder
     */
    public function scopeForUpdate(Builder $builder, LegalUpdate $update): Builder
    {
        return $builder->whereRelation('legalUpdates', 'id', $update->id);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForAssessMetrics(Builder $builder): Builder
    {
        /** @var AssessStatsService */
        $statsService = app(AssessStatsService::class);

        return $statsService->buildRiskRatingQuery($builder);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForDetailedAssessMetrics(Builder $builder): Builder
    {
        /** @var AssessStatsService */
        $statsService = app(AssessStatsService::class);

        return $statsService->buildDetailedRiskRatingQuery($builder);
    }

    /**
     * @param Builder $query
     * @param float   $north
     * @param float   $east
     * @param float   $south
     * @param float   $west
     * @param int     $precision
     *
     * @return Builder
     */
    public function scopeWithinBounds(
        Builder $query,
        float $north,
        float $east,
        float $south,
        float $west,
        int $precision
    ) {
        /** @var Builder */
        return $query // ->where('geo_lng', '>=', $west)
            // ->where('geo_lng', '<=', $east)
            // ->where('geo_lat', '<=', $north)
            // ->where('geo_lat', '>=', $south)
            ->whereRaw("`geo_lng` >= {$west} and `geo_lng` <= {$east} and `geo_lat` <= {$north} and `geo_lat` >= {$south}")
            ->whereNotNull('geohash')
            ->groupBy(['geohash_prefix'])
            ->selectRaw("MIN(id) as id, MIN(title) as title, MIN(place_type_id) as place_type_id, AVG(geo_lat) AS geo_lat, AVG(geo_lng) AS geo_lng, COUNT(*) AS quantity, SUBSTRING(geohash FROM 1 FOR {$precision}) AS geohash_prefix");
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeAutocompiled(Builder $builder): Builder
    {
        return $builder->where('auto_compiled', true);
    }

    /**
     * @param Builder    $builder
     * @param array<int> $referenceIds
     *
     * @return Builder
     */
    public function scopeForReferences(Builder $builder, array $referenceIds): Builder
    {
        return $builder->whereRelation('references', fn ($q) => $q->whereKey($referenceIds));
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
        return $this->provideFilter(LibryoFilter::class);
    }

    /**
     * Removes the compiled citations from the cache.
     *
     * @return self
     */
    public function forgetCompilationCache(): self
    {
        Cache::tags([config('cache-keys.compilation.tag')])
            ->forget(config('cache-keys.compilation.cache_key_prefix') . ':' . $this->id);
        Cache::tags([config('cache-keys.compilation.tag')])
            ->forget(config('cache-keys.compilation.cache_key_prefix_ids') . ':' . $this->id);
        Cache::forget(config('cache-keys.compilation.legal_report_cache_ids') . ':' . $this->id);

        return $this;
    }

    /**
     * @return bool
     */
    public function canBeRecompiled(): bool
    {
        return !is_null($this->library);
    }

    /**
     * @return bool
     */
    public function markAsNeedingRecompilation(): bool
    {
        if ($this->needs_recompilation) {
            return true;
        }

        return $this->update(['needs_recompilation' => true]);
    }

    /**
     * Updates the needs recompilation flag for an libryo.
     *
     * @param bool $needsRecompilation
     *
     * @return void
     */
    public function updateNeedsRecompilation(bool $needsRecompilation): void
    {
        if ($this->needs_recompilation !== $needsRecompilation) {
            $this->needs_recompilation = $needsRecompilation;
            $this->save();
        }
    }

    /**
     * Gets the parent libraries and its' parent libraries all the way up the hierarchy.
     *
     * @return Collection<Library>
     **/
    public function getLibraryAncestors(): Collection
    {
        /** @var Collection<Library> */
        $collection = (new Library())->newCollection();
        if (!$this->library) {
            return $collection;
        }

        $collection->add($this->library);
        $this->_getLibraryAncestors($collection, $this->library);
        foreach ($collection as $library) {
            unset($library['parents']);
        }

        return $collection;
    }

    /**
     * Recursively walk up the ancestor tree to get all libraries.
     *
     * @param Collection<Library> $libraries
     * @param Library             $library
     *
     * @return Collection<Library>
     **/
    private function _getLibraryAncestors(Collection $libraries, Library $library): Collection
    {
        if (!$library->parents->isEmpty()) {
            foreach ($library->parents as $parent) {
                $libraries->add($parent);
                $this->_getLibraryAncestors($libraries, $parent);
            }
        }

        return $libraries;
    }

    /**
     * Check whether this libryo has the module enabled.
     *
     * @return bool
     */
    public function hasModule(string $module): bool
    {
        return $this->settings['modules'][$module] ?? false;
    }

    /**
     * Check whether this libryo has the assess module enabled.
     *
     * @return bool
     */
    public function hasActionsModule(): bool
    {
        return $this->hasModule('actions');
    }

    /**
     * Check whether this libryo has the tasks module enabled.
     *
     * @return bool
     */
    public function hasTasksModule(): bool
    {
        return $this->hasModule('tasks');
    }

    /**
     * Check whether this libryo has the assess module enabled.
     *
     * @return bool
     */
    public function hasAssessModule(): bool
    {
        return $this->hasModule('comply');
    }

    /**
     * Enables the given module for this org.
     *
     * @param LibryoModule $module
     *
     * @return bool
     */
    public function enableModule(LibryoModule $module): bool
    {
        return $this->updateSetting('modules.' . $module->value, true);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->deactivated;
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

    /**
     * Check if the module is enabled but hidden from non-super users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isApplicabilityAccessible(User $user): bool
    {
        $hidden = $this->getSetting('modules.hide_applicability', false);

        return $this->auto_compiled && (!$hidden || $user->isMySuperUser());
    }
}
