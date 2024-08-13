<?php

namespace App\Models\Ontology;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Enums\Ontology\CategoryType as CategoryTypeEnum;
use App\Http\ModelFilters\Ontology\CategoryFilter;
use App\Models\AbstractModel;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemCategory;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Models\Ontology\Pivots\CategoryContextQuestion;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use App\Models\Traits\AttachedToLibryoAndOrganisation;
use App\Models\Traits\HasClosureTable;
use App\Traits\HasModelCache;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCategory
 */
class Category extends AbstractModel implements Auditable
{
    use HasModelCache;
    use SoftDeletes;
    use HasClosureTable;
    use Filterable;
    use \OwenIt\Auditing\Auditable;
    use AttachedToLibryoAndOrganisation;

    protected $table = 'corpus_categories';

    /**
     * For Category we're allowing the user to set the ID, so we can have nice numbers.
     *
     * @var array<string>
     */
    protected $guarded = ['created_at', 'updated_at'];

    /** @var Collection<string> */
    public Collection $path;

    public function getClosureTableClass(): string
    {
        return CategoryClosure::class;
    }

    public function closureTableUsesPositions(): bool
    {
        return false;
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(CategoryReferenceDraft::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new CategoryReference())->getTable(), 'category_id', 'reference_id')
            ->using(CategoryReference::class)
            ->orderBy('category_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new CategoryContextQuestion())->getTable(), 'category_id', 'context_question_id')
            ->using(CategoryContextQuestion::class);
    }

    /**
     * @return BelongsToMany
     */
    public function assessmentItems(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentItem::class, (new AssessmentItemCategory())->getTable())
            ->using(AssessmentItemCategory::class);
    }

    /**
     * @return HasMany
     */
    public function actionsAsSubject(): HasMany
    {
        return $this->hasMany(ActionArea::class, 'subject_category_id');
    }

    /**
     * @return HasMany
     */
    public function actionsAsControl(): HasMany
    {
        return $this->hasMany(ActionArea::class, 'control_category_id');
    }

    /**
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(CategoryDescription::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTagging(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereIn('category_type_id', [
            CategoryTypeEnum::SUBJECT->value,
            CategoryTypeEnum::ACTIVITY->value,
            CategoryTypeEnum::TOPICS->value,
        ]);
    }

    public function scopeForParent(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeNoParent(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNull('parent_id');
    }

    /**
     * @param Builder $builder
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $builder, Libryo $libryo): Builder
    {
        return $builder->whereHas('references', function ($q) use ($libryo) {
            $q->active()->forLibryo($libryo);
        });
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereHas('references', function ($q) use ($organisation) {
            $q->active()->forOrganisation($organisation);
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
        return $builder->whereRelation('references', function ($q) use ($organisation, $user) {
            $q->forOrganisationUserAccess($organisation, $user);
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
        return $query->where('display_label', 'LIKE', "%{$search}%");
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeControl(Builder $query): Builder
    {
        /** @var Builder */
        return $query->where('category_type_id', CategoryTypeEnum::CONTROL->value);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeTopics(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereIn('category_type_id', [CategoryTypeEnum::ACTIVITY->value, CategoryTypeEnum::SUBJECT->value]);
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     * @param Libryo|null  $libryo
     *
     * @return Builder
     */
    public function scopeControlsForSelectors(Builder $query, Organisation $organisation, ?Libryo $libryo = null): Builder
    {
        $baseQuery = function ($q) use ($libryo, $organisation) {
            $q->forLibryoOrOrganisation($libryo, $organisation)
                ->typeControl();
        };

        /** @var Builder */
        return $query->whereNotNull('parent_id')->where(function ($q) use ($baseQuery) {
            $q->where($baseQuery)
                ->orWhereHas('descendants', $baseQuery);
        });
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     * @param Libryo|null  $libryo
     *
     * @return Builder
     */
    public function scopeTopicsForSelectors(Builder $query, Organisation $organisation, ?Libryo $libryo = null): Builder
    {
        $baseQuery = function ($q) use ($libryo, $organisation) {
            $q->forLibryoOrOrganisation($libryo, $organisation)
                ->typeTopics();
        };

        /** @var Builder */
        return $query->where(function ($q) use ($baseQuery) {
            $q->where($baseQuery)
                ->orWhereHas('descendants', $baseQuery);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Get the cache classes.
     *
     * @return array<int, class-string>
     */
    protected static function modelCaches(): array
    {
        return [
            CategorySelectorCache::class,
        ];
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(CategoryFilter::class);
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
            'label' => $this->label,
            'display_label' => $this->display_label,
        ];
    }
}
