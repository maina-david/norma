<?php

namespace App\Models\Geonames;

use App\Enums\Application\ApplicationType;
use App\Http\ModelFilters\Geonames\LocationFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Pivots\LocationSource;
use App\Models\Arachno\Source;
use App\Models\Compilation\Pivots\RequirementCollectionClosure;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Geonames\Pivots\LocationLocation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Pivots\FileLocation;
use App\Models\Traits\HasClosureTable;
use App\Models\Traits\HasTitleLikeScope;
use App\Traits\HasCached;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperLocation
 */
class Location extends AbstractModel
{
    use Filterable;
    use HasTitleLikeScope;
    use SoftDeletes;
    use HasCached;
    use HasClosureTable;

    protected $table = 'corpus_locations';

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
        return $this->hasMany(LocationReferenceDraft::class);
    }

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    /**
     * Alias for type, here for legacy purposes.
     *
     * @return BelongsTo
     */
    public function locationType(): BelongsTo
    {
        return $this->type();
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(static::class, 'location_country_id');
    }

    /**
     * @return BelongsToMany
     */
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(static::class, (new LocationLocation())->getTable(), 'descendant', 'ancestor')
            ->using(LocationLocation::class)
            ->wherePivot('depth', '>', 0)
            ->orderByPivot('depth');
    }

    /**
     * @return BelongsToMany
     */
    public function descendants(): BelongsToMany
    {
        return $this->belongsToMany(static::class, (new LocationLocation())->getTable(), 'ancestor', 'descendant')
            ->using(LocationLocation::class)
            ->wherePivot('depth', '>', 0)
            ->orderByPivot('depth');
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new LocationReference())->getTable())
            ->using(LocationReference::class);
    }

    /**
     * @return HasMany
     */
    public function normas(): HasMany
    {
        return $this->hasMany(Norma::class, 'location_id');
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, (new FileLocation())->getTable(), 'location_id', 'file_id')
            ->using(FileLocation::class);
    }

    /**
     * @return BelongsToMany
     */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class)
            ->using(LocationSource::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Query scope to check ancestors.
     *
     * @param Builder               $query
     * @param array<string, string> $levels: ['level_2' => 'California', 'level_3' => 'Los Angeles']
     *
     * @return Builder
     **/
    public function scopeAncestorNames(Builder $query, array $levels): Builder
    {
        foreach ($levels as $levelKey => $name) {
            $name = urldecode($name);
            $query->where(function ($builder) use ($levelKey, $name) {
                $level = (int) explode('_', $levelKey)[1];
                $builder->whereHas('parent', function ($query) use ($level, $name) {
                    $query->where('level', $level);
                    $query->nameMatches($name);
                });
                $builder->orWhereHas('parent.parent', function ($query) use ($level, $name) {
                    $query->where('level', $level);
                    $query->nameMatches($name);
                });
            });
        }

        return $query;
    }

    /**
     * Query scope to check alternate names.
     *
     * @param Builder $query
     * @param string  $name
     *
     * @return Builder
     */
    public function scopeNameMatches(Builder $query, string $name): Builder
    {
        return $query->where(function ($builder) use ($name) {
            $name = urldecode($name);
            $builder->where('title', $name)
                ->orWhereJsonContains('alternate_names', $name)
                ->orWhere('slug', $name);
        });
    }

    /**
     * Query scope to check alternate names.
     *
     * @param Builder  $query
     * @param Location $location
     *
     * @return Builder
     */
    public function scopeIsOrHasAncestor(Builder $query, Location $location): Builder
    {
        return $query->where(function ($q) use ($location) {
            $q->whereKey($location->id);
            $q->orWhereHas('ancestors', fn ($q) => $q->whereKey($location->id));
        });
    }

    /**
     * Query scope to check for parent.
     *
     * @param Builder $query
     * @param int     $parentId
     *
     * @return Builder
     */
    public function scopeForParent(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeNoParent(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNull('parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Exclude the given permissions from the autogenerated CRUD permissions.
     *
     * @return array<string, array<int, string>>
     */
    public static function excludeFromCrud(): array
    {
        $exclude = ['create', 'update', 'delete'];

        return [
            ApplicationType::admin()->value => $exclude,
            ApplicationType::collaborate()->value => $exclude,
            ApplicationType::my()->value => $exclude,
        ];
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(LocationFilter::class);
    }

    /**
     * @return string
     */
    public function getBreadcrumbs(string $separator = ' > '): string
    {
        return $this->ancestors
            ->reverse()
            ->map(function ($l) {
                /** @var Location $l */
                return $l->title . ($l->type ? ' (' . $l->type->title . ')' : '');
            })
            ->implode($separator);
    }

    /**
     * Get the closure table.
     *
     * @return string
     */
    public function getClosureTableClass(): string
    {
        return RequirementCollectionClosure::class;
    }

    /**
     * Define if closure uses positions.
     *
     * @return bool
     */
    public function closureTableUsesPositions(): bool
    {
        return false;
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
            'alternate_names' => $this->alternate_names,
        ];
    }
}
