<?php

namespace App\Models\Ontology;

use App\Http\ModelFilters\Ontology\TagFilter;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Corpus\Pivots\ReferenceTag;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Traits\HasFulltextSearchScope;
use App\Models\Traits\HasTitleLikeScope;
use App\Scopes\Metadata\TagScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperTag
 */
class Tag extends AbstractModel implements Auditable
{
    use Filterable;
    use HasFulltextSearchScope;
    use HasTitleLikeScope;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_tags';

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TagScope());
    }

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
        return $this->hasMany(ReferenceTagDraft::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new ReferenceTag())->getTable(), 'tag_id', 'reference_id')
            ->using(ReferenceTag::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */
    /**
     * @param Builder $query
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $query, Norma $norma): Builder
    {
        return $query->whereHas('references', function ($q) use ($norma) {
            $q->active()->forNorma($norma);
        });
    }

    // /**
    //  * @param Builder      $query
    //  * @param Organisation $organisation
    //  *
    //  * @return Builder
    //  */
    // public function scopeForOrganisation(Builder $query, Organisation $organisation): Builder
    // {
    //     return $query->whereHas('references', function ($q) use ($organisation) {
    //         $q->forOrganisation($organisation);
    //     });
    // }

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
        return $query->searchTitle($search)
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
    public function modelFilter(): string
    {
        return $this->provideFilter(TagFilter::class);
    }
}
