<?php

namespace App\Models\Assess;

use App\Enums\Assess\RiskRating;
use App\Http\ModelFilters\Assess\AssessmentItemFilter;
use App\Models\AbstractModel;
use App\Models\Assess\Pivots\AssessmentItemCategory;
use App\Models\Assess\Pivots\AssessmentItemContextQuestion;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Bookmarks\AssessmentItemBookmark;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasCategory;
use App\Traits\UsesArchiving;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperAssessmentItem
 */
class AssessmentItem extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use ApiQueryFilterable;
    use Filterable;
    use HasCategory;
    use EncodesHashId;
    use UsesArchiving;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_assessment_items';

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

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        return $this->toDescription();
    }

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
        return $this->hasMany(AssessmentItemReferenceDraft::class);
    }

    /**
     * @return BelongsToMany
     */
    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new AssessmentItemContextQuestion())->getTable())
            ->using(AssessmentItemContextQuestion::class);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, (new AssessmentItemCategory())->getTable())
            ->using(AssessmentItemCategory::class);
    }

    /**
     * @return HasMany
     */
    public function assessmentItemCategories(): HasMany
    {
        return $this->hasMany(AssessmentItemCategory::class, 'assessment_item_id');
    }

    /**
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(AssessmentItemDescription::class, 'assessment_item_id');
    }

    /**
     * @return HasMany
     */
    public function guidanceNotes(): HasMany
    {
        return $this->hasMany(GuidanceNote::class);
    }

    /**
     * @return BelongsTo
     */
    public function legalDomain(): BelongsTo
    {
        return $this->belongsTo(LegalDomain::class, 'legal_domain_id');
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new AssessmentItemReference())->getTable(), 'assessment_item_id', 'reference_id')
            ->using(AssessmentItemReference::class)
            ->typeCitation();
    }

    /**
     * @return HasMany
     */
    public function assessmentResponses(): HasMany
    {
        return $this->hasMany(AssessmentItemResponse::class, 'assessment_item_id');
    }

    /**
     * @return HasMany
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(AssessmentItemBookmark::class, 'assessment_item_id');
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeRiskHigh(Builder $builder): Builder
    {
        return $builder->where('risk_rating', RiskRating::high()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeRiskMedium(Builder $builder): Builder
    {
        return $builder->where('risk_rating', RiskRating::medium()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeRiskLow(Builder $builder): Builder
    {
        return $builder->where('risk_rating', RiskRating::low()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeRiskNotRated(Builder $builder): Builder
    {
        return $builder->where('risk_rating', RiskRating::notRated()->value);
    }

    /**
     * @param Builder $builder
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearchComponents(Builder $builder, string $search): Builder
    {
        return $builder->where(function ($query) use ($search) {
            /** @var \Illuminate\Database\Query\Expression $cols */
            $cols = DB::raw('CONCAT_WS(" ", component_1, component_2, component_3, component_4, component_5, component_6, component_7, component_8)');

            $query->where('title', 'like', "%{$search}%")
                ->orWhere($cols, 'like', '%' . $search . '%');
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
        return $builder->where(function ($q) use ($domainIds) {
            $q->whereHas('legalDomain', fn ($q) => $q->whereKey($domainIds));
            $q->orWhereHas('legalDomain.parent', fn ($q) => $q->whereKey($domainIds));
            $q->orWhereHas('legalDomain.topParent', fn ($q) => $q->whereKey($domainIds));
        });
    }

    /**
     * Get the assessment that are possible to be attached to the given norma which is not yet compiled.
     *
     * @param \Illuminate\Database\Eloquent\Builder   $builder
     * @param \App\Models\Customer\Norma             $norma
     * @param \App\Models\Compilation\ContextQuestion $question
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePossibleForUncompiledNorma(Builder $builder, Norma $norma, ContextQuestion $question): Builder
    {
        return $builder
            ->where(function ($query) use ($norma) {
                $query->notUserGenerated()->orWhere('organisation_id', $norma->organisation_id);
            })
            ->whereHas('references', function ($query) use ($question, $norma) {
                $query->possibleForUncompiledNorma($norma)
                    ->whereRelation('contextQuestions', 'id', $question->id);
            });
    }

    /**
     * Scope for all possible assessment item based on the currently compiled references.
     *
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopePossibleForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder
            ->where(function ($query) use ($norma) {
                $query->notUserGenerated()->orWhere('organisation_id', $norma->organisation_id);
            })
            ->whereHas('references', function ($q) use ($norma) {
                $q->active();
                $q->forNorma($norma);
            });
    }

    /**
     * Scope for all possible assessment item based on the currently compiled references in the organisation.
     *
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopePossibleForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder
            ->where(function ($query) use ($organisation) {
                $query->notUserGenerated()->orWhere('organisation_id', $organisation->id);
            })
            ->whereHas('references', function ($q) use ($organisation) {
                $q->active();
                $q->forOrganisation($organisation);
            });
    }

    /**
     * Scope for assessment items where a response has not been created yet for the given norma
     * (mostly to be used in combination with other scopes, e.g. possibleForNorma).
     *
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeNoResponsesForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->whereDoesntHave('assessmentResponses', function ($q) use ($norma) {
            $q->whereRelation('norma', 'id', $norma->id);
        });
    }

    /**
     * Scope for assessment items where a response has not been created yet for the given organisation's normas.
     * (mostly to be used in combination with other scopes, e.g. possibleForOrganisation).
     *
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeNoResponsesForNormasInOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereDoesntHave('assessmentResponses', function ($q) use ($organisation) {
            $q->whereRelation('norma', function ($q) use ($organisation) {
                $q->where('organisation_id', $organisation->id);
            });
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotUserGenerated(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNull('organisation_id');
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
        return $this->provideFilter(AssessmentItemFilter::class);
    }

    /**
     * @return string
     **/
    public function toDescription(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $descriptions = [
            trim($this->component_1 ?? ''),
            trim($this->component_2 ?? ''),
            trim($this->component_3 ?? ''),
            trim($this->component_4 ?? ''),
            trim($this->component_5 ?? ''),
            trim($this->component_6 ?? ''),
            trim($this->component_7 ?? ''),
            trim($this->component_8 ?? ''),
        ];

        /** @var string */
        $descriptions = preg_replace('/\s+/', ' ', implode(' ', $descriptions));

        $descriptions = trim($descriptions);

        $deleted = $this->deleted_at
            ? sprintf(' (%s)', __('assess.assessment_item.deleted_on', ['date' => $this->deleted_at->format('d M Y')]))
            : '';

        return ucfirst(strtolower($descriptions)) . $deleted;
    }

    /**
     * Get the explanation of the assessment item for the given norma.
     *
     * @param \App\Models\Customer\Norma $norma
     *
     * @return \App\Models\Assess\AssessmentItemDescription|null
     */
    public function explanationForNorma(Norma $norma): ?AssessmentItemDescription
    {
        /** @var \App\Models\Geonames\Location $location */
        $location = $norma->location;

        /** @var AssessmentItemDescription|null */
        return $this->descriptions()
            ->where(function ($query) use ($location) {
                $query->whereNull('location_id')
                    ->when($location->location_country_id ?? false, fn ($builder) => $builder->orWhere('location_id', $location->location_country_id))
                    ->when($location->id ?? false, fn ($builder) => $builder->orWhere('location_id', $location->id));
            })
            ->orderBy('location_id', 'desc')
            ->first();
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
            'component_1' => $this->component_1,
            'component_2' => $this->component_2,
            'component_3' => $this->component_3,
            'component_4' => $this->component_4,
            'component_5' => $this->component_5,
            'component_6' => $this->component_6,
            'component_7' => $this->component_7,
            'component_8' => $this->component_8,
        ];
    }
}
