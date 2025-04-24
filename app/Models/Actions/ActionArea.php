<?php

namespace App\Models\Actions;

use App\Http\ModelFilters\Actions\ActionAreaFilter;
use App\Models\AbstractModel;
use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use App\Traits\UsesArchiving;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperActionArea
 */
class ActionArea extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    use Filterable;
    use UsesArchiving;

    /** @var array<string, string> */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function controlCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'control_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subjectCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subject_category_id');
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new ActionAreaReference())->getTable(), 'action_area_id', 'reference_id')
            ->using(ActionAreaReference::class)
            ->typeCitation();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'action_area_id');
    }

    /**
     * Scope for all possible items based on the currently compiled references.
     *
     * @param Builder                                 $builder
     * @param Norma                                  $norma
     * @param \App\Models\Compilation\ContextQuestion $question
     *
     * @return Builder
     */
    public function scopePossibleForNormaInApplicability(Builder $builder, Norma $norma, ContextQuestion $question): Builder
    {
        return $builder->active()
            ->whereHas('references', function ($query) use ($question, $norma) {
                $references = ContextQuestionReference::where('context_question_id', $question->id)->select('reference_id');

                $query->active()
                    ->forNorma($norma)
                    ->whereIn(qualify_column(Reference::class, 'id'), $references);
            });
    }

    /**
     * Scope for all possible items based on the currently compiled references.
     *
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopePossibleForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->active()->whereHas('references', fn ($query) => $query->active()->forNorma($norma));
    }

    /**
     * Scope for all possible items based on the currently compiled references in the organisation.
     *
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopePossibleForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->active()->whereHas('references', fn ($query) => $query->active()->forOrganisation($organisation));
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(ActionAreaFilter::class);
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
        ];
    }

    /**
     * Get the compliance records associated with the action area.
     *
     * @return HasMany
     */
    public function compliances(): HasMany
    {
        return $this->hasMany(ActionAreaCompliance::class);
    }

    /**
     * Get the current compliance record associated with the action area.
     *
     * @return HasOne
     */
    public function currentCompliance(): HasOne
    {
        return $this->hasOne(ActionAreaCompliance::class)->where('current', true);
    }
}
