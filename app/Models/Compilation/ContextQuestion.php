<?php

namespace App\Models\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Enums\Compilation\ContextQuestionCategory;
use App\Http\ModelFilters\Compilation\ContextQuestionFilter;
use App\Models\AbstractModel;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Compilation\Pivots\ContextQuestionRequirementsCollection;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryContextQuestion;
use App\Models\Tasks\Task;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasCategory;
use App\Models\Traits\HasComments;
use App\Traits\UsesArchiving;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperContextQuestion
 */
class ContextQuestion extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use Filterable;
    use ApiQueryFilterable;
    use HasCategory;
    use HasComments;
    use EncodesHashId;
    use UsesArchiving;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_context_questions';

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
     * Get the question attribute.
     *
     * @return string
     */
    public function getQuestionAttribute(): string
    {
        return $this->toQuestion();
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
    public function activities(): HasMany
    {
        return $this->hasMany(ApplicabilityActivity::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(ContextQuestionReferenceDraft::class);
    }

    /**
     * @return HasMany
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ContextQuestionNorma::class, 'context_question_id');
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, (new CategoryContextQuestion())->getTable())
            ->using(CategoryContextQuestion::class);
    }

    /**
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(ContextQuestionDescription::class, 'context_question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mainDescription(): HasOne
    {
        return $this->hasOne(ContextQuestionDescription::class)->orderBy('location_id', 'desc');
    }

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'corpus_context_question_location');
    }

    /**
     * @return BelongsToMany
     */
    public function requirementsCollections(): BelongsToMany
    {
        return $this->belongsToMany(RequirementsCollection::class, (new ContextQuestionRequirementsCollection())->getTable(), 'context_question_id', 'location_id')
            ->using(ContextQuestionRequirementsCollection::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new ContextQuestionReference())->getTable(), 'context_question_id', 'reference_id')
            ->using(ContextQuestionReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function normas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new ContextQuestionNorma())->getTable(), 'context_question_id', 'place_id')
            ->using(ContextQuestionNorma::class)
            ->withPivot(['answer', 'last_answered_by', 'last_answered_at']);
    }

    /**
     * @return BelongsToMany
     */
    public function normasYes()
    {
        return $this->normas()
            ->wherePivot('answer', ContextQuestionAnswer::yes()->value);
    }

    /**
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for searching question.
     *
     * @param Builder $builder
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeQuestionLike(Builder $builder, string $search): Builder
    {
        /** @var Builder */
        return $builder->whereRaw('CONCAT_WS(" ", `prefix`, `predicate`, `pre_object`, `object`, `post_object`) like ?', ['%' . $search . '%']);
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->whereRelation('normas', 'id', $norma->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereRelation('normas', function ($q) use ($organisation) {
            $q->active();
            $q->where('organisation_id', $organisation->id);
        });
    }

    /**
     * @param Builder                     $builder
     * @param \App\Models\Customer\Norma $norma
     * @param array<int, int>             $answers
     *
     * @return Builder
     */
    public function scopeForNormaWithAnswers(Builder $builder, Norma $norma, array $answers): Builder
    {
        return $builder->whereRelation('normas', function ($query) use ($answers, $norma) {
            $query->active()->where('id', $norma->id);

            if (!empty($answers)) {
                $query->whereIn((new ContextQuestionNorma())->qualifyColumn('answer'), $answers);
            }
        });
    }

    /**
     * @param Builder         $builder
     * @param Organisation    $organisation
     * @param array<int, int> $answers
     *
     * @return Builder
     */
    public function scopeForOrganisationWithAnswers(Builder $builder, Organisation $organisation, array $answers): Builder
    {
        return $builder->whereRelation('normas', function ($q) use ($answers, $organisation) {
            $q->active();
            $q->where('organisation_id', $organisation->id);
            if (!empty($answers)) {
                $q->whereIn((new ContextQuestionNorma())->qualifyColumn('answer'), $answers);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the question form of the model.
     *
     * @return string
     **/
    public function toQuestion(): string
    {
        $descriptions = [
            trim($this->prefix ?? ''),
            trim($this->predicate ?? ''),
            trim($this->pre_object ?? ''),
            trim($this->object ?? ''),
            trim($this->post_object ?? ''),
        ];

        /** @var string */
        $descriptions = preg_replace('/\s+/', ' ', implode(' ', $descriptions));

        $descriptions = trim($descriptions) . '?';

        return ucfirst(strtolower($descriptions));
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(ContextQuestionFilter::class);
    }

    public function getCategoryText(): ?string
    {
        $str = '';
        switch ($this->category_id) {
            case ContextQuestionCategory::industryAndProductContext()->value:
                $str = 'Industry and Product Context';
                break;
            case ContextQuestionCategory::airQualityFactors()->value:
                $str = 'Air Quality Factors';
                break;
            case ContextQuestionCategory::biodiversityFactors()->value:
                $str = 'Biodiversity Factors';
                break;
            case ContextQuestionCategory::hazardousSubstancesFactors()->value:
                $str = 'Hazardous Substances Factors';
                break;
            case ContextQuestionCategory::wasteFactors()->value:
                $str = 'Waste Factors';
                break;
            case ContextQuestionCategory::waterFactors()->value:
                $str = 'Water Factors';
                break;
            case ContextQuestionCategory::locationAndSiteContext()->value:
                $str = 'Location and Site Context';
                break;
            case ContextQuestionCategory::employeeContext()->value:
                $str = 'Employee Context';
                break;
            case ContextQuestionCategory::machineryAndEquipmentFactors()->value:
                $str = 'Machinery and Equipment Factors';
                // no break
            case ContextQuestionCategory::foodSafety()->value:
                $str = 'Food Safety';
                break;
            default:
                $str = '';
                break;
        }

        return $str;
    }

    /**
     * Get the explanation of the context question item for the given norma.
     *
     * @param \App\Models\Customer\Norma $norma
     *
     * @return \App\Models\Compilation\ContextQuestionDescription|null
     */
    public function explanationForNorma(Norma $norma): ?ContextQuestionDescription
    {
        /** @var \App\Models\Geonames\Location $location */
        $location = $norma->location;

        /** @var ContextQuestionDescription|null */
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
            'prefix' => $this->prefix,
            'predicate' => $this->predicate,
            'pre_object' => $this->pre_object,
            'object' => $this->object,
            'post_object' => $this->post_object,
        ];
    }
}
