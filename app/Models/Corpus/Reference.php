<?php

namespace App\Models\Corpus;

use App\Contracts\Translation\HasTranslations;
use App\Enums\Corpus\ReferenceLinkType;
use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Http\ModelFilters\Corpus\ReferenceFilter;
use App\Models\AbstractModel;
use App\Models\Actions\ActionArea;
use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Bookmarks\ReferenceBookmark;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Library;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Compilation\Pivots\LibraryReference;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceClosure;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Pivots\ReferenceRequirementsCollection;
use App\Models\Corpus\Pivots\ReferenceTag;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\CompiledNormaReference;
use App\Models\Customer\Pivots\NormaReference;
use App\Models\Customer\Pivots\NormaRequirementsCollection;
use App\Models\Geonames\Location;
use App\Models\Lookups\Translation;
use App\Models\Notify\Reminder;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use App\Models\Ontology\Tag;
use App\Models\Requirements\Consequence;
use App\Models\Requirements\Pivots\ConsequenceReference;
use App\Models\Requirements\ReferenceRequirement;
use App\Models\Requirements\ReferenceRequirementDraft;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use App\Models\Tasks\Task;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\AttachedToNormaAndOrganisation;
use App\Models\Traits\HasCategory;
use App\Models\Traits\HasClosureTable;
use App\Models\Traits\HasComments;
use App\Models\Traits\UsesUid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;

/**
 * @property \App\Models\Corpus\Pivots\ReferenceReference $pivot
 * @property int                                          $ref_requirement_count
 * @property int                                          $requirement_draft_count
 * @property int                                          $summary_count
 * @property int                                          $summary_draft_count
 *
 * @mixin IdeHelperReference
 */
class Reference extends AbstractModel implements HasTranslations
{
    use SoftDeletes;
    use HasComments;
    use Filterable;
    use ApiQueryFilterable;
    use UsesUid;
    use HasCategory;
    use HasClosureTable;
    use Searchable;
    use AttachedToNormaAndOrganisation;

    /** @var string */
    protected $table = 'corpus_references';

    /** @var array<string,string> */
    protected $casts = [
        'has_rights' => 'bool',
        'has_amendments' => 'bool',
        'has_exceptions' => 'bool',
        'has_procedural' => 'bool',
        'has_technical' => 'bool',
        'has_consequences' => 'bool',
        'has_rights_update' => 'bool',
        'has_amendments_update' => 'bool',
        'has_exceptions_update' => 'bool',
        'has_procedural_update' => 'bool',
        'has_technical_update' => 'bool',
        'has_consequences_update' => 'bool',
        'is_section' => 'bool',
    ];

    /**
     * Dynamically added attribute when searching fulltext search.
     *
     * @var array<string>|null
     */
    public ?array $_searchHighlights = null;

    public ?float $_searchScore = null;

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new LegalDomainReference())->getTable())
            ->using(LegalDomainReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomainDrafts(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new LegalDomainReferenceDraft())->getTable())
            ->using(LegalDomainReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, (new ReferenceTag())->getTable())
            ->using(ReferenceTag::class);
    }

    /**
     * @return BelongsToMany
     */
    public function tagDrafts(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, (new ReferenceTagDraft())->getTable())
            ->using(ReferenceTagDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new LocationReference())->getTable())
            ->using(LocationReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function locationDrafts(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new LocationReferenceDraft())->getTable())
            ->using(LocationReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return BelongsToMany
     */
    public function requirementsCollections(): BelongsToMany
    {
        return $this->belongsToMany(RequirementsCollection::class, (new ReferenceRequirementsCollection())->getTable(), 'reference_id', 'location_id')
            ->using(ReferenceRequirementsCollection::class);
    }

    /**
     * @return BelongsTo
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'work_id');
    }

    /**
     * @return BelongsToMany
     */
    public function libraries()
    {
        return $this->belongsToMany(Library::class, (new LibraryReference())->getTable(), 'register_item_id', 'library_id')
            ->using(LibraryReference::class);
    }

    /**
     * @return HasOne
     */
    public function summary(): HasOne
    {
        return $this->hasOne(Summary::class, 'reference_id');
    }

    /**
     * @return HasOne
     */
    public function summaryDraft(): HasOne
    {
        return $this->hasOne(ReferenceSummaryDraft::class, 'reference_id');
    }

    /**
     * @return BelongsTo
     */
    public function citation(): BelongsTo
    {
        return $this->belongsTo(Citation::class, 'referenceable_id');
    }

    /**
     * @return BelongsToMany
     */
    public function normas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new NormaReference())->getTable(), 'reference_id', 'place_id')
            ->using(NormaReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function compiledNormas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new CompiledNormaReference())->getTable(), 'reference_id', 'place_id')
            ->using(CompiledNormaReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function assessmentItems(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentItem::class, (new AssessmentItemReference())->getTable(), 'reference_id', 'assessment_item_id')
            ->using(AssessmentItemReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function assessmentItemDrafts(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentItem::class, (new AssessmentItemReferenceDraft())->getTable(), 'reference_id', 'assessment_item_id')
            ->using(AssessmentItemReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * @return MorphMany
     */
    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'register_item_id');
    }

    /**
     * @return HasOne
     */
    public function englishTranslation(): HasOne
    {
        return $this->hasOne(Translation::class, 'register_item_id')->ofMany([
            'id' => 'min',
        ], function ($query) {
            $query->where('target_language', 'en');
        });
    }

    /**
     * @return BelongsToMany
     */
    public function raisesConsequenceGroups(): BelongsToMany
    {
        return $this->belongsToMany(self::class, (new ReferenceReference())->getTable(), 'parent_id', 'child_id')
            ->using(ReferenceReference::class)
            ->wherePivot('link_type', ReferenceLinkType::CONSEQUENCE->value);
    }

    /**
     * @return BelongsToMany
     */
    public function consequences(): BelongsToMany
    {
        return $this->belongsToMany(Consequence::class, (new ConsequenceReference())->getTable(), 'reference_id', 'consequence_id')
            ->using(ConsequenceReference::class);
    }

    /**
     * @return BelongsTo
     */
    public function parentReference(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function childReferences(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')
            ->typeCitation()
            ->orderedPosition();
    }

    /**
     * @return BelongsToMany
     */
    public function linkedParents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, (new ReferenceReference())->getTable(), 'child_id', 'parent_id')
            ->using(ReferenceReference::class)
            ->withPivot(['link_type', 'parent_work_id', 'child_work_id'])
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
                ReferenceType::consequenceGroup()->value,
            ]);
    }

    /**
     * @return BelongsToMany
     */
    public function linkedChildren(): BelongsToMany
    {
        return $this->belongsToMany(self::class, (new ReferenceReference())->getTable(), 'parent_id', 'child_id')
            ->using(ReferenceReference::class)
            ->withPivot(['link_type', 'parent_work_id', 'child_work_id'])
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
                ReferenceType::consequenceGroup()->value,
            ]);
    }

    /**
     * @return BelongsToMany
     */
    public function linkedConsequenceParents(): BelongsToMany
    {
        return $this->linkedParents()->wherePivot('link_type', ReferenceLinkType::CONSEQUENCE->value);
    }

    /**
     * @return BelongsToMany
     */
    public function excludedInNormas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, 'place_reference_include_exclude', 'reference_id', 'place_id')
            ->wherePivot('include', false);
    }

    /**
     * @return BelongsToMany
     */
    public function includedInNormas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, 'place_reference_include_exclude', 'reference_id', 'place_id')
            ->wherePivot('include', true);
    }

    /**
     * @return BelongsToMany
     */
    public function excludedInLibraries(): BelongsToMany
    {
        return $this->belongsToMany(Library::class, 'library_register_item_include_exclude', 'register_item_id', 'library_id')
            ->wherePivot('include_exclude', false);
    }

    /**
     * @return BelongsToMany
     */
    public function includedInLibraries(): BelongsToMany
    {
        return $this->belongsToMany(Library::class, 'library_register_item_include_exclude', 'register_item_id', 'library_id')
            ->wherePivot('include_exclude', true);
    }

    /**
     * @return BelongsToMany
     */
    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new ContextQuestionReference())->getTable(), 'reference_id', 'context_question_id')
            ->using(ContextQuestionReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function contextQuestionDrafts(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new ContextQuestionReferenceDraft())->getTable(), 'reference_id', 'context_question_id')
            ->using(ContextQuestionReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, (new CategoryReference())->getTable(), 'reference_id', 'category_id')
            ->using(CategoryReference::class)
            ->orderBy('category_type_id');
    }

    /**
     * @return BelongsToMany
     */
    public function categoriesForTagging(): BelongsToMany
    {
        return $this->categories()->forTagging();
    }

    /**
     * @return BelongsToMany
     */
    public function categoryDrafts(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, (new CategoryReferenceDraft())->getTable(), 'reference_id', 'category_id')
            ->using(CategoryReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /**
     * @return HasMany
     */
    public function collaborateComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contentExtracts(): HasMany
    {
        return $this->hasMany(ReferenceContentExtract::class);
    }

    public function htmlContent(): HasOne
    {
        return $this->hasOne(ReferenceContent::class, 'reference_id');
    }

    public function contentDraft(): HasOne
    {
        return $this->hasOne(ReferenceContentDraft::class, 'reference_id');
    }

    public function refSelector(): HasOne
    {
        return $this->hasOne(ReferenceSelector::class, 'reference_id');
    }

    public function refTitleText(): HasOne
    {
        return $this->hasOne(ReferenceTitleText::class, 'reference_id');
    }

    public function refRequirement(): HasOne
    {
        return $this->hasOne(ReferenceRequirement::class, 'reference_id');
    }

    public function requirementDraft(): HasOne
    {
        return $this->hasOne(ReferenceRequirementDraft::class, 'reference_id');
    }

    public function refPlainText(): HasOne
    {
        return $this->hasOne(ReferenceText::class, 'reference_id');
    }

    /**
     * @return HasMany
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(ReferenceBookmark::class, 'reference_id');
    }

    /**
     * @return HasMany
     */
    public function contentVersions(): HasMany
    {
        return $this->hasMany(ReferenceContentVersion::class, 'reference_id');
    }

    public function latestTocItem(): HasOne
    {
        return $this->hasOne(TocItem::class, 'uid', 'uid')
            ->latestOfMany();
    }

    /**
     * @return BelongsToMany
     */
    public function actionAreas(): BelongsToMany
    {
        return $this->belongsToMany(ActionArea::class, (new ActionAreaReference())->getTable(), 'reference_id', 'action_area_id')
            ->using(ActionAreaReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function actionAreaDrafts(): BelongsToMany
    {
        return $this->belongsToMany(ActionArea::class, (new ActionAreaReferenceDraft())->getTable(), 'reference_id', 'action_area_id')
            ->using(ActionAreaReferenceDraft::class)
            ->withPivot(['change_status', 'annotation_source_id']);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCompilable(Builder $query): Builder
    {
        return $query->has('refRequirement');
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), ReferenceStatus::active()->value);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeCitation(Builder $query): Builder
    {
        return $query->where('type', ReferenceType::citation()->value);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeWork(Builder $query): Builder
    {
        return $query->where('type', ReferenceType::work()->value);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeHasActiveWork(Builder $query): Builder
    {
        return $query->whereHas('work', function ($q) {
            $q->active();
        });
    }

    /**
     * @param Builder    $query
     * @param array<int> $locationIds
     *
     * @return Builder
     */
    public function scopeForLocations(Builder $query, array $locationIds): Builder
    {
        return $query->whereHas('locations', function ($q) use ($locationIds) {
            $q->whereKey($locationIds);
        });
    }

    /**
     * @param Builder    $query
     * @param array<int> $domainIds
     *
     * @return Builder
     */
    public function scopeForDomains(Builder $query, array $domainIds): Builder
    {
        return $query->whereHas('legalDomains', function ($q) use ($domainIds) {
            $q->whereKey($domainIds);
        });
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->whereHas('normas', function ($q) use ($norma) {
            $q->whereKey($norma->id);
        });
    }

    /**
     * @param Builder         $query
     * @param array<int, int> $normaIds
     *
     * @return Builder
     */
    public function scopeForMultipleNormas(Builder $query, array $normaIds): Builder
    {
        return $query->whereHas('normas', function ($q) use ($normaIds) {
            $q->whereKey($normaIds);
        });
    }

    /**
     * Apply the scope to filter the references that only apply to this given norma location.
     *
     * @param Builder     $builder
     * @param Norma|null $norma
     *
     * @return Builder
     */
    public function scopeforNormaLocations(Builder $builder, ?Norma $norma): Builder
    {
        if ($norma->compilationSetting->use_collections ?? false) {
            /** @var Norma $norma */
            $collections = NormaRequirementsCollection::where('place_id', $norma->id)->pluck('collection_id')->all();

            $builder->where(function ($query) use ($collections) {
                $query->whereHas('locations', fn ($builder) => $builder->whereIn('id', $collections))
                    ->orDoesntHave('locations');
            });
        }

        return $builder;
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereHas('normas.organisation', function ($q) use ($organisation) {
            $q->whereKey($organisation->id);
        });
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccess(Builder $query, Organisation $organisation, User $user): Builder
    {
        return $query->whereHas('normas', function ($q) use ($organisation, $user) {
            $q->active()->whereHas('organisation', function ($q) use ($organisation, $user) {
                $q->whereKey($organisation->id);
                $q->userHasAccess($user);
            });
        });
    }

    /**
     * @param Builder    $builder
     * @param array<int> $tagIds
     *
     * @return Builder
     */
    public function scopeForTags(Builder $builder, array $tagIds): Builder
    {
        return $builder->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereKey($tagIds);
        });
    }

    /**
     * @param Builder    $builder
     * @param array<int> $tagIds
     *
     * @return Builder
     */
    public function scopeForTagsWithTrashed(Builder $builder, array $tagIds): Builder
    {
        return $builder->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereKey($tagIds);
            $q->withTrashed();
        });
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNormaAutocompiledBase(Builder $builder, Norma $norma): Builder
    {
        $settings = $norma->compilationSetting;

        return $builder
            ->whereHas('work', fn ($query) => $query->whereNull('organisation_id'))
            ->where(function ($q) use ($norma, $settings) {
                $q->when($settings->use_collections, function ($q) use ($norma) {
                    $q->whereHas('locations', function ($q) use ($norma) {
                        $collections = $norma->requirementsCollections;
                        $q->whereKey($collections->modelKeys());
                    });
                });

                $q->when($settings->use_legal_domains, function ($q) use ($norma) {
                    $q->whereHas('legalDomains', function ($q) use ($norma) {
                        $q->whereIn('top_parent_id', $norma->legalDomains->modelKeys());
                    });
                });

                $q->compilable()
                    ->typeCitation()
                    ->active()
                    ->forActiveWork();
            });
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNormaAutocompiled(Builder $builder, Norma $norma): Builder
    {
        $settings = $norma->compilationSetting;

        $builder->forNormaAutocompiledBase($norma)
            ->when($settings->use_context_questions, function ($q) use ($norma, $settings) {
                $q->where(function ($q) use ($norma, $settings) {
                    $q->whereHas('contextQuestions', function ($q) use ($norma) {
                        $q->whereHas('normasYes', function ($q) use ($norma) {
                            $q->whereKey($norma->id);
                        });
                    })
                        ->when($settings->include_no_context_questions, function ($q) {
                            $q->orDoesntHave('contextQuestions');
                        });
                });
            });

        // We shall perform the include-exclude after populating the compiled references table.

        return $builder;
    }

    /**
     * Scope to get included references.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Norma           $norma
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForNormaIncluded(Builder $builder, Norma $norma): Builder
    {
        return $builder->compilable()
            ->typeCitation()
            ->active()
            ->forActiveWork()
            ->whereHas('includedInNormas', fn ($query) => $query->whereKey($norma->id));
    }

    /**
     * Scope to get include-exclude references.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Norma           $norma
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForNormaExcluded(Builder $builder, Norma $norma): Builder
    {
        return $builder->compilable()
            ->typeCitation()
            ->active()
            ->forActiveWork()
            ->whereHas('excludedInNormas', fn ($query) => $query->whereKey($norma->id));
    }

    /**
     * Get the references that are possible to be attached to the given norma.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Norma           $norma
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePossibleForUncompiledNorma(Builder $builder, Norma $norma): Builder
    {
        $norma->load(['legalDomains', 'location']);

        /** @var Location $location */
        $location = $norma->location;

        return $builder->typeCitation()
            ->when($location, function ($builder) use ($location) {
                $locations = $location->ancestorsWithSelf()->select($location->qualifyColumn('id'));
                $builder->whereHas('locations', fn ($query) => $query->whereIn('id', $locations));
            })
            ->when($norma->legalDomains->isNotEmpty(), function ($q) use ($norma) {
                $q->forDomains($norma->legalDomains->modelKeys());
            })
            ->compilable()
            ->forActiveWork();
    }

    /**
     * Scope for all references that are in libraries that are ancestor's of the norma's embryo. Old way of compiling...
     *
     * @param Builder $query
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeInNormaLibraries(Builder $query, Norma $norma): Builder
    {
        $libraries = $norma->getLibraryAncestors();

        $query->typeCitation();
        $query->compilable();

        $query->whereHas('libraries', function ($q) use ($libraries) {
            $q->whereIn('id', $libraries->pluck('id')->toArray());
        });

        $query->whereHas('legalDomains', function ($q) use ($norma) {
            $q->whereKey($norma->legalDomains->pluck('id')->toArray());
        });

        return $query;
    }

    /**
     * Using a scope here so we only need to change in one place once ordering changes.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeOrderedPosition(Builder $builder): Builder
    {
        return $builder->orderBy('volume', 'ASC')
            ->orderBy('start', 'ASC')
            ->orderBy('level', 'ASC');
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeForActiveWork(Builder $builder): Builder
    {
        return $builder->whereHas('work', fn ($q) => $q->active());
    }

    /**
     * @param Builder $builder
     * @param Work    $work
     *
     * @return Builder
     */
    public function scopeForWork(Builder $builder, Work $work): Builder
    {
        return $builder->where('work_id', $work->id);
    }

    /**
     * @param Builder   $query
     * @param Reference $reference
     *
     * @return Builder
     */
    public function scopePossibleParentsOf(Builder $query, Reference $reference): Builder
    {
        $query->where('work_id', $reference->work_id)
            ->where('id', '!=', $reference->id)
            ->whereIn('type', ReferenceType::allowedParents())
            ->where('volume', '<=', $reference->volume ?? 1)
            ->where('start', '<=', $reference->start ?? 0);

        $query->when(!$reference->level, function ($q) {
            $q->whereNotNull('level');
        });

        $query->when($reference->level, function ($q) use ($reference) {
            $q->where('level', '<', $reference->level);
        });

        $query->orderBy('volume', 'desc')->orderBy('start', 'desc');

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * {@inheritDoc}
     *
     * @see HasClosureTable Trait
     */
    public function getClosureTableClass(): string
    {
        return ReferenceClosure::class;
    }

    /**
     * Method has to be implemented from abstract method in HasClosureTable Trait.
     * References that have parent_id null (mostly the work reference) need to have position 0, but
     * if you use parent positions, then it tries to find a reference from another work and then the highest value.
     *
     * @return bool
     */
    public function closureTableUsesPositions(): bool
    {
        return true;
    }

    /**
     * Overrides the method in the HasClosureTable Trait.
     *
     * @return string
     *
     * @see HasClosureTable Trait
     */
    public function getPositionWithinParentColumn(): string
    {
        return 'parent_position';
    }

    /**
     * Override method in HasClosureTable Trait.
     *
     * @param static $entity
     *
     * @return int
     */
    public static function getLatestPosition($entity): int
    {
        $positionColumn = 'parent_position';

        /** @var static|null */
        $latest = $entity->select($positionColumn)
            ->when($entity->parent_id, fn ($q) => $q->where('parent_id', '=', $entity->parent_id))
            ->when(!$entity->parent_id, fn ($q) => $q->whereNull('parent_id')->where('work_id', $entity->work_id))
            ->latest($positionColumn)
            ->first();

        $position = $latest !== null ? $latest->parent_position : -1;

        return $position + 1;
    }

    /**
     * Find the possible parent.
     *
     * @param array<array-key, string> $columns
     *
     * @return Reference|null
     */
    public function findPossibleParent(array $columns = ['*']): ?self
    {
        /** @var Reference|null */
        return self::possibleParentsOf($this)->first($columns);
    }

    /**
     * Only implemented because it's required when using UsesUid, but UID is copied from TocItem.
     *
     * @return string
     */
    public function getUidString(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(ReferenceFilter::class);
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === ReferenceStatus::pending()->value;
    }

    /**
     * @return bool
     */
    public function hasConsequenceRelations(): bool
    {
        /** @var static|null */
        $consequenceParent = $this->linkedParents->first(function ($ref) {
            /** @var Reference $ref */
            return $ref->pivot->link_type === ReferenceLinkType::CONSEQUENCE->value;
        });

        return !is_null($consequenceParent);
    }

    /**
     * {@inheritDoc}
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->has('refPlainText')
            ->has('htmlContent')
            ->has('refRequirement')
            ->with(['refPlainText', 'htmlContent']);
    }

    /**
     * Get the engine used to index the model.
     *
     * @return \Laravel\Scout\Engines\Engine
     */
    public function searchableUsing(): Engine
    {
        return app(EngineManager::class)->engine('meilisearch');
    }

    /**
     * {@inheritDoc}
     */
    public function shouldBeSearchable(): bool
    {
        return ReferenceType::citation()->is($this->type)
            && ReferenceStatus::active()->is($this->status)
            && $this->refRequirement()->exists();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->load(['refPlainText', 'htmlContent']);

        /** @var string $content */
        $content = preg_replace('/\s+/', ' ', strip_tags($this->htmlContent?->cached_content ?? ''));
        $content = trim($content);

        return [
            'id' => $this->id,
            'title' => $this->refPlainText?->plain_text,
            'cached_content' => mb_substr($content, 0, 65534),
            'work_id' => $this->work_id,
        ];
    }

    /**
     * Get the IDs of both parent and child for the links.
     *
     * @param \App\Enums\Corpus\ReferenceLinkType $type
     *
     * @return array<int, int>
     */
    public function getLinkedTypeIDs(ReferenceLinkType $type): array
    {
        $table = (new ReferenceReference())->getTable();

        $query = DB::table($table)
            ->select('child_id as id')
            ->where('parent_id', $this->id)
            ->where('link_type', $type->value);

        /** @var array<int, int> */
        return DB::table($table)
            ->select('parent_id as id')
            ->where('child_id', $this->id)
            ->where('link_type', $type->value)
            ->union($query)
            ->pluck('id')
            ->all();
    }
}
