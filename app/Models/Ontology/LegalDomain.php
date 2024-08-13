<?php

namespace App\Models\Ontology;

use App\Cache\Ontology\Collaborate\LegalDomainSelectorCache;
use App\Enums\Application\ApplicationType;
use App\Http\ModelFilters\Ontology\LegalDomainFilter;
use App\Models\AbstractModel;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\LegalDomainLibryo;
use App\Models\Geonames\Location;
use App\Models\Ontology\Pivots\LegalDomainLocation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Pivots\FileLegalDomain;
use App\Traits\HasCached;
use App\Traits\HasModelCache;
use App\Traits\UsesArchiving;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperLegalDomain
 */
class LegalDomain extends AbstractModel implements Auditable
{
    use HasModelCache;
    use SoftDeletes;
    use Filterable;
    use HasCached;
    use UsesArchiving;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_legal_domains';

    /** @var Collection<string> */
    public Collection $path;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new LegalDomainLocation())->getTable(), 'legal_domain_id', 'location_id')
            ->using(LegalDomainLocation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(LegalDomainReferenceDraft::class);
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function categoryParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'category_parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function topParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'top_parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function libryos(): BelongsToMany
    {
        return $this->belongsToMany(Libryo::class, (new LegalDomainLibryo())->getTable(), 'legal_domain_id', 'place_id')
            ->using(LegalDomainLibryo::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new LegalDomainReference())->getTable())
            ->using(LegalDomainReference::class);
    }

    /**
     * @return HasMany
     */
    public function assessmentItems(): HasMany
    {
        return $this->hasMany(AssessmentItem::class, 'legal_domain_id');
    }

    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, (new FileLegalDomain())->getTable())
            ->using(FileLegalDomain::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|null                              $locationId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLocation(Builder $builder, ?int $locationId): Builder
    {
        if ($locationId && $loc = Location::with('ancestorsWithSelfUnordered')->find($locationId)) {
            $locations = $loc->ancestorsWithSelfUnordered->pluck('id')->all();

            $builder->where(function ($query) use ($locations) {
                $query
                    ->where(function ($builder) {
                        $builder->doesntHave('locations')->doesntHave('topParent.locations');
                    })
                    ->orWhere(function ($builder) use ($locations) {
                        $builder->whereHas('locations', fn ($q) => $q->whereKey($locations))
                            ->orWhereHas('topParent.locations', fn ($q) => $q->whereKey($locations));
                    });
            });
        }

        return $builder;
    }

    /**
     * @param Builder $query
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $query, Libryo $libryo): Builder
    {
        return $query->whereRelation('libryos', 'id', $libryo->id);
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $query, Organisation $organisation): Builder
    {
        return $query->whereRelation('libryos.organisation', 'id', $organisation->id);
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
        return $query->whereRelation('libryos', function ($q) use ($organisation, $user) {
            $q->userHasAccess($user);
            $q->forOrganisation($organisation->id);
        });
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNoParent(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @param Builder $builder
     * @param string  $title
     *
     * @return Builder
     */
    public function scopeTitleLike(Builder $builder, string $title): Builder
    {
        return $builder->where('title', 'LIKE', "%{$title}%");
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
        $fields = ['create', 'update', 'delete'];

        return [
            ApplicationType::admin()->value => $fields,
            ApplicationType::collaborate()->value => $fields,
            ApplicationType::my()->value => $fields,
        ];
    }

    /**
     * Get the cache classes.
     *
     * @return array<int, class-string>
     */
    protected static function modelCaches(): array
    {
        return [
            LegalDomainSelectorCache::class,
        ];
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(LegalDomainFilter::class);
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
