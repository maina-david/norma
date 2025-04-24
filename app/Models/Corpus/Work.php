<?php

namespace App\Models\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Enums\Corpus\WorkStatus;
use App\Http\ModelFilters\Corpus\WorkFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Corpus\Pivots\WorkWork;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaWork;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateWork;
use App\Models\Notify\UpdateCandidateLegislation;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\HasFulltextSearchScope;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Traits\UsesUid;
use App\Services\Corpus\DocumentTypes;
use App\Services\Storage\WorkStorageProcessor;
use App\Traits\HandlesUTF8;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperWork
 */
class Work extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use Filterable;
    use HasFulltextSearchScope;
    use HasTitleLikeScope;
    use ApiQueryFilterable;
    use UsesUid;
    use HandlesUTF8;

    /** @var string */
    protected $table = 'corpus_works';

    /** @var float|null */
    public ?float $_relevanceScore = null;

    /** @var array<string, string> */
    protected $casts = [
        'work_date' => 'date',
        'effective_date' => 'date',
        'repealed_date' => 'date',
        'comment_date' => 'date',
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
     * @return HasMany
     */
    public function references(): HasMany
    {
        return $this->hasMany(Reference::class, 'work_id')
            ->typeCitation()
            ->orderedPosition();
    }

    /**
     * NG: Don't use this!! Only here for legacy/backwards compatibility purposes.
     *
     * @return HasMany
     */
    public function registerItems(): HasMany
    {
        return $this->references();
    }

    /**
     * @return BelongsTo
     */
    public function activeExpression(): BelongsTo
    {
        return $this->belongsTo(WorkExpression::class, 'active_work_expression_id');
    }

    /**
     * @return HasMany
     */
    public function expressions(): HasMany
    {
        return $this->hasMany(WorkExpression::class, 'work_id');
    }

    /**
     * @return BelongsToMany
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(static::class, (new WorkWork())->getTable(), 'child_work_id', 'parent_work_id')
            ->using(WorkWork::class);
    }

    /**
     * @return BelongsToMany
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(static::class, (new WorkWork())->getTable(), 'parent_work_id', 'child_work_id')
            ->using(WorkWork::class);
    }

    /**
     * @return BelongsTo
     */
    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    /**
     * @return HasOne
     */
    public function workReference(): HasOne
    {
        /** @var HasOne */
        return $this->hasOne(Reference::class, 'work_id')
            ->where('type', ReferenceType::work()->value);
    }

    /**
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * @return HasMany
     */
    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class, 'work_id');
    }

    /**
     * @return BelongsTo
     */
    public function activeDoc(): BelongsTo
    {
        return $this->belongsTo(Doc::class, 'active_doc_id');
    }

    /**
     * @return BelongsToMany
     */
    public function normas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new NormaWork())->getTable(), 'work_id', 'place_id')
            ->using(NormaWork::class);
    }

    /**
     * @return BelongsTo
     */
    public function catalogueDoc(): BelongsTo
    {
        return $this->belongsTo(CatalogueDoc::class, 'uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maintainingDocs(): BelongsToMany
    {
        return $this->belongsToMany(
            Doc::class,
            (new UpdateCandidateLegislation())->getTable(),
            'work_id',
            'doc_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maintainingUpdates(): BelongsToMany
    {
        return $this->belongsToMany(LegalUpdate::class, (new LegalUpdateWork())->getTable())
            ->using(LegalUpdateWork::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active works.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '=', WorkStatus::active()->value);
    }

    /**
     * @param Builder              $query
     * @param Norma               $norma
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $query, Norma $norma, array $referenceFilters = []): Builder
    {
        return $query->whereHas('references', function ($q) use ($norma, $referenceFilters) {
            $q->forNorma($norma);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        })
            ->active();
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $query, Organisation $organisation, array $referenceFilters = []): Builder
    {
        return $query->whereHas('references', function ($q) use ($organisation, $referenceFilters) {
            $q->forOrganisation($organisation);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        })
            ->active();
    }

    /**
     * @param Builder $query
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeCachedForNorma(Builder $query, Norma $norma): Builder
    {
        return $query->whereRelation('normas', fn ($q) => $q->whereKey($norma->id));
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeCachedForOrganisationUserAccess(Builder $query, Organisation $organisation, User $user): Builder
    {
        return $query->whereHas('normas', function ($q) use ($organisation, $user) {
            $q->active()->whereHas('organisation', function ($q) use ($organisation, $user) {
                $q->whereKey($organisation->id);
                $q->userHasAccess($user);
            });
        });
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccess(Builder $query, Organisation $organisation, User $user, array $referenceFilters = []): Builder
    {
        return $query->whereHas('references', function ($q) use ($organisation, $referenceFilters, $user) {
            $q->forOrganisationUserAccess($organisation, $user);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        })
            ->active();
    }

    /**
     * @param Builder              $query
     * @param Norma               $norma
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopePrimaryForNorma(Builder $query, Norma $norma, array $referenceFilters = []): Builder
    {
        // running this as a union is a lot more efficient
        $q = static::forNorma($norma, $referenceFilters)->doesntHave('parents')->select(['id'])
            ->union(
                static::whereHas('children.references', function ($q) use ($norma, $referenceFilters) {
                    $q->forNorma($norma);
                    if (!empty($referenceFilters)) {
                        $q->filter($referenceFilters);
                    }
                })->select(['id'])
            );

        $rawSql = 'id in (select id from (' . $q->toSql() . ') as u)';

        return $query->whereRaw($rawSql, $q->getBindings())
            ->active()
            ->orderBy('title');
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopePrimaryForOrganisation(Builder $query, Organisation $organisation, User $user, array $referenceFilters = []): Builder
    {
        // $q = static::forOrganisation($organisation, $referenceFilters)->doesntHave('parents')->select(['id'])
        //     ->union(
        //         static::whereHas('children.references', function ($q) use ($organisation, $referenceFilters) {
        //             $q->forOrganisation($organisation);
        //             if (!empty($referenceFilters)) {
        //                 $q->filter($referenceFilters);
        //             }
        //         })->select(['id'])
        //     );

        // $rawSql = 'id in (select id from (' . $q->toSql() . ') as u)';

        // return $query->whereRaw($rawSql, $q->getBindings())
        //     ->active()
        //     ->orderBy('title');

        $allWorkIds = Work::cachedForOrganisationUserAccess($organisation, $user)->get('id')->toArray();

        return $query->where(function ($q) use ($allWorkIds) {
            $q->whereKey($allWorkIds);
            $q->orWhereHas('children', function ($q) use ($allWorkIds) {
                $q->whereKey($allWorkIds);
            });
        })->active()
            ->doesntHave('parents');

        // $referenceTable = (new Reference)->getTable();
        // $workPivot = (new WorkWork)->getTable();
        // $workTable = (new Work)->getTable();

        // $q = Reference::forOrganisation($organisation)
        //     ->whereRaw("not exists (select 1 from $workPivot where $referenceTable.work_id = $workPivot.child_work_id)")
        //     ->select('work_id')
        //     ->union(
        //         Reference::forOrganisation($organisation)
        //             ->rightJoin($workPivot, $referenceTable . '.work_id', '=', $workPivot . '.child_work_id')
        //             ->leftjoin($workTable, $workTable . '.id', '=', $workPivot . '.child_work_id')
        //             ->whereRaw($workTable . '.deleted_at is null')
        //             ->select('parent_work_id')
        //     );
        // $rawSql = 'id in (' . $q->toSql() . ')';
        // return $query->whereRaw($rawSql, $q->getBindings())
        //     ->active();
    }

    /**
     * @param Builder              $query
     * @param array<string, mixed> $filters
     * @param string               $search
     * @param User                 $user
     * @param Norma|null          $norma
     * @param Organisation|null    $organisation
     * @param bool                 $includeContent
     *
     * @return Builder
     */
    public function scopeForRequirements(
        Builder $query,
        array $filters,
        string $search,
        User $user,
        ?Norma $norma,
        ?Organisation $organisation,
        bool $includeContent = false,
    ): Builder {
        $refFilters = Arr::except($filters, ['works']);
        if ($search !== '') {
            $referenceQuery = $norma
                ? Reference::forNorma($norma)
                : Reference::forOrganisation($organisation); // @phpstan-ignore-line
            $referenceQuery->filter($refFilters);

            $refIds = $referenceQuery->pluck('id')->all();

            $searchFilter = function ($engine, string $query, array $options) {
                $options['matchingStrategy'] = 'all';

                return $engine->search($query, $options);
            };

            /** @var LengthAwarePaginator<Reference> */
            $items = empty($refIds) ? new LengthAwarePaginator((new Reference())->newCollection(), 0, 25) : Reference::search($search, $searchFilter)->whereIn('id', $refIds)->paginate(9999, 'page', 1);
            $refFilters['ids'] = $items->modelKeys();
            if (empty($refFilters['ids'])) {
                // no search results, so should return empty..
                $refFilters['ids'] = [0];
            }
        }

        if (isset($filters['works'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereKey($filters['works'])
                    ->orWhereHas('children', fn ($q) => $q->whereKey($filters['works']));
            });
        }

        return $norma
            ? $query->primaryForNorma($norma, $refFilters)->withRelationsForNorma($norma, $includeContent, $refFilters, isset($filters['works']) ? ['works' => $filters['works']] : [])
            : $query->primaryForOrganisation($organisation, $user, $refFilters)->withRelationsForOrganisation($organisation, $user, $includeContent, $refFilters, isset($filters['works']) ? ['works' => $filters['works']] : []);
    }

    /**
     * @param Builder              $query
     * @param Norma               $norma
     * @param bool                 $includeContent
     * @param array<string, mixed> $referenceFilters
     * @param array<string, mixed> $childrenFilters
     *
     * @return Builder
     */
    public function scopeWithRelationsForNorma(
        Builder $query,
        Norma $norma,
        bool $includeContent = false,
        array $referenceFilters = [],
        array $childrenFilters = [],
    ): Builder {
        $forNormaCallback = function ($q) use ($norma, $referenceFilters, $childrenFilters) {
            $q->active()->forNorma($norma);
            if (!empty($referenceFilters)) {
                $q->whereHas('references', fn ($q) => $q->filter($referenceFilters));
            }
            if (isset($childrenFilters['works'])) {
                $q->where(function ($q) use ($childrenFilters) {
                    $q->whereKey($childrenFilters['works'])
                        ->orWhereHas('parents', fn ($q) => $q->whereKey($childrenFilters['works']));
                });
            }
        };
        $referencesCallback = function ($q) use ($norma, $referenceFilters) {
            $q->active()->forNorma($norma);
            $columns = ['id', 'referenceable_id', 'work_id', 'created_at', 'updated_at'];
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
            $q->select($columns);
        };
        $emptyCallback = fn ($q) => null;

        if ($includeContent) {
            $query->with([
                'references.htmlContent:reference_id,cached_content',
                'children.references.htmlContent:reference_id,cached_content',
            ]);
        }

        return $query->with([
            'children' => $forNormaCallback,
            'children.references' => $referencesCallback,
            'references' => $referencesCallback,
            'children.references.citation' => $emptyCallback,
            'children.references.refPlainText' => $emptyCallback,
            'references.citation' => $emptyCallback,
            'references.refPlainText' => $emptyCallback,
            'references.legalDomains' => $emptyCallback,
            'references.locations.type' => $emptyCallback,
            'references.locations.country' => $emptyCallback,
            'children.references.legalDomains' => $emptyCallback,
            'children.references.locations.type' => $emptyCallback,
            'children.references.locations.country' => $emptyCallback,
            'primaryLocation' => $emptyCallback,
            'children.primaryLocation' => $emptyCallback,
        ]);
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     * @param array<string, mixed> $childrenFilters
     *
     * @return Builder
     */
    public function scopeWithRelationsForOrganisation(
        Builder $query,
        Organisation $organisation,
        User $user,
        bool $includeContent = false,
        array $referenceFilters = [],
        array $childrenFilters = [],
    ): Builder {
        $forOrgCallback = function ($q) use ($organisation, $user, $referenceFilters, $childrenFilters) {
            $q->forOrganisationUserAccess($organisation, $user);
            if (!empty($referenceFilters)) {
                $q->whereHas('references', fn ($q) => $q->filter($referenceFilters));
            }
            if (isset($childrenFilters['works'])) {
                $q->where(function ($q) use ($childrenFilters) {
                    $q->whereKey($childrenFilters['works'])
                        ->orWhereHas('parents', fn ($q) => $q->whereKey($childrenFilters['works']));
                });
            }
        };
        $referencesCallback = function ($q) use ($organisation, $user, $referenceFilters) {
            $q->forOrganisationUserAccess($organisation, $user);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
            $columns = ['id', 'referenceable_id', 'work_id', 'created_at', 'updated_at'];
            $q->select($columns);
        };
        $emptyCallback = fn ($q) => null;

        if ($includeContent) {
            $query->with([
                'references.htmlContent:reference_id,cached_content',
                'children.references.htmlContent:reference_id,cached_content',
            ]);
        }

        return $query->with([
            'children' => $forOrgCallback,
            'children.references' => $referencesCallback,
            'references' => $referencesCallback,
            'children.references.citation' => $emptyCallback,
            'children.references.refPlainText' => $emptyCallback,
            'references.citation' => $emptyCallback,
            'references.refPlainText' => $emptyCallback,
            'references.legalDomains' => $emptyCallback,
            'references.locations.type' => $emptyCallback,
            'references.locations.country' => $emptyCallback,
            'children.references.legalDomains' => $emptyCallback,
            'children.references.locations.type' => $emptyCallback,
            'children.references.locations.country' => $emptyCallback,
        ]);
    }

    /**
     * @param Builder    $query
     * @param array<int> $locationIds
     *
     * @return Builder
     */
    public function scopeForCountries(Builder $query, array $locationIds): Builder
    {
        return $query->whereHas('references', function ($q) use ($locationIds) {
            $q->whereHas('locations', function ($q) use ($locationIds) {
                $q->whereIn('location_country_id', $locationIds);
            });
        });
    }

    /**
     * @param Builder $query
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeForSearch(Builder $query, string $search): Builder
    {
        /** @var Builder */
        return $query->where(function ($q) use ($search) {
            $q->searchTitle($search);
            $q->orWhere(function ($q) use ($search) {
                $q->searchColumn($search, 'title_translation');
            });
        })
            // need to add this for fulltext search
            ->addSelect(['id', 'title']);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return ($this->source_id ?? '') . '__' . $this->source_unique_id;
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(WorkFilter::class);
    }

    public function withRelationsForNorma(Norma $norma): self
    {
        $this->with([
            'references' => function ($q) use ($norma) {
                $q->forNorma($norma);
            },
            'references.locations',
            'references.refPlainText',
        ]);

        return $this;
    }

    /**
     * Returns the current work expression for the given date.
     * Defaults to today if no date given.
     *
     * @return WorkExpression|null
     **/
    public function getCurrentExpression(?Carbon $date = null): ?WorkExpression
    {
        if ($this->activeExpression) {
            return $this->activeExpression;
        }

        $date = $date ? $date->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        /** @var WorkExpression|null */
        return $this->expressions
            ->where('start_date', '<=', $date)
            ->last();
    }

    /**
     * Get the stored cached volume.
     *
     * @param int $volume
     *
     * @return string|null
     */
    public function getCachedVolume(int $volume): ?string
    {
        return (new WorkStorageProcessor())->getCachedWorkVolume($this, $volume);
    }

    /**
     * Get the Regulation type's name with each word uppercase'd.
     *
     * @return string
     **/
    public function getTypeName(): string
    {
        // @codeCoverageIgnoreStart
        if (!$this->work_type) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        $num = DocumentTypes::getInt($this->work_type);

        return ucwords(DocumentTypes::getPrettyString($num));
    }

    /**
     * Check if the document has a PDF source.
     *
     * @return bool
     */
    public function isPDFSource(): bool
    {
        if (!$this->relationLoaded('activeExpression.sourceDocument')) {
            $this->load(['activeExpression.sourceDocument']);
        }

        return ($this->activeExpression->sourceDocument->mime_type ?? '') === 'application/pdf';
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
            'title_translation' => $this->title_translation,
        ];
    }

    /**
     * Transform the data before performing an audit.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function transformAudit(array $data): array
    {
        $data['old_values'] = $this->arrayToUTF8($data['old_values'] ?? []);
        $data['new_values'] = $this->arrayToUTF8($data['new_values'] ?? []);

        return $data;
    }
}
