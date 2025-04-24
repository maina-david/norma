<?php

namespace App\Models\Compilation;

use App\Http\ModelFilters\Compilation\LibraryFilter;
use App\Models\AbstractModel;
use App\Models\Compilation\Pivots\LibraryLibrary;
use App\Models\Compilation\Pivots\LibraryReference;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateLibrary;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperLibrary
 */
class Library extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasTitleLikeScope;
    use Filterable;

    /** @var array<string, string> */
    protected $casts = [
        'auto_compiled' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany
     */
    public function normas(): HasMany
    {
        return $this->hasMany(Norma::class);
    }

    /**
     * @return BelongsToMany
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, (new LibraryLibrary())->getTable(), 'child_id', 'parent_id')
            ->using(LibraryLibrary::class);
    }

    /**
     * @return BelongsToMany
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, (new LibraryLibrary())->getTable(), 'parent_id', 'child_id')
            ->using(LibraryLibrary::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new LibraryReference())->getTable(), 'library_id', 'register_item_id')
            ->using(LibraryReference::class);
    }

    /**
     * @return BelongsToMany
     */
    public function excludedReferences(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, 'library_register_item_include_exclude', 'library_id', 'register_item_id')
            ->wherePivot('include_exclude', false);
    }

    /**
     * @return BelongsToMany
     */
    public function includedReferences(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, 'library_register_item_include_exclude', 'library_id', 'register_item_id')
            ->wherePivot('include_exclude', true);
    }

    /**
     * @return BelongsToMany
     */
    public function legalUpdates(): BelongsToMany
    {
        return $this->belongsToMany(
            LegalUpdate::class,
            (new LegalUpdateLibrary())->getTable(),
            'library_id',
            'register_notification_id'
        )->using(LegalUpdateLibrary::class);
    }

    /**
     * NB: Only here for legacy purposes - can be removed once the table is removed.
     *
     * @return BelongsToMany
     */
    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, 'library_context_question', 'library_id', 'context_question_id')
            ->withPivot(['answer']);
    }
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param int     $organisationId
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('normas', function ($q) use ($organisationId) {
            $q->forOrganisation($organisationId);
        });
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
        return $this->provideFilter(LibraryFilter::class);
    }

    /**
     * Get all the descendants.
     *
     * @param Library|null             $parent
     * @param Collection<Library>|null $descendants
     *
     * @return Collection<Library>
     */
    public function descendants(?Library $parent = null, ?Collection $descendants = null): Collection
    {
        $parent = $parent ?? $this;
        /** @var Collection<Library> */
        $descendants = $descendants ?? $this->newCollection();
        $parent->load('children');

        foreach ($parent->children as $child) {
            $descendants->push($child);
            $this->descendants($child, $descendants);
        }

        return $descendants;
    }

    /**
     * @param Library|null                 $parent
     * @param Collection<int, Norma>|null $normas
     *
     * @return Collection<int, Norma>
     */
    public function applicableNormas(?Library $parent = null, ?Collection $normas = null): Collection
    {
        $parent = $parent ?? $this;

        // only push the current norma if it's the first item.
        if (!$normas) {
            $parent->load(['normas']);
            $normas = $parent->normas;
        }

        $parent->load(['children.normas']);

        foreach ($parent->children as $child) {
            $normas = $normas->merge($child->normas);
            $this->applicableNormas($child, $normas);
        }

        return $normas->filter(fn ($l) => $l->isActive());
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
}
