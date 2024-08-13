<?php

namespace App\Models\Storage\My;

use App\Enums\Storage\FileType;
use App\Models\AbstractModel;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\Pivots\FileReference;
use App\Models\Storage\My\Pivots\FolderLegalDomain;
use App\Models\Storage\My\Pivots\FolderLocation;
use App\Models\Traits\AttachedToLibryoAndOrganisation;
use App\Models\Traits\HasFolderType;
use App\Models\Traits\LimitsLegalDomains;
use App\Models\Traits\LimitsLocations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperFolder
 */
class Folder extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use HasFolderType;
    use LimitsLegalDomains;
    use LimitsLocations;
    use AttachedToLibryoAndOrganisation;

    /** @var array<string, string> */
    protected $casts = [
        'is_root' => 'bool',
        'for_attachments' => 'bool',
    ];

    /** @var string|null */
    public ?string $originalTitle = null;

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return BelongsTo
     */
    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class);
    }

    /**
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return BelongsToMany
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, (new FileReference())->getTable(), 'file_id', 'register_item_id')
            ->using(FileReference::class);
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'folder_parent_id')
            ->orderBy('title');
    }

    /**
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class)
            ->orderBy('title');
    }

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new FolderLocation())->getTable(), 'folder_id', 'location_id')
            ->using(FolderLocation::class);
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new FolderLegalDomain())->getTable(), 'folder_id', 'legal_domain_id')
            ->using(FolderLegalDomain::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder                $builder
     * @param Organisation|null|null $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, ?Organisation $organisation = null): Builder
    {
        $builder->typeOrganisation()->whereNull('organisation_id');

        if ($organisation) {
            $builder->orWhere(function ($q) use ($organisation) {
                $q->typeOrganisation()
                    ->whereRelation('organisation', 'id', $organisation->id);
            });
        }

        return $builder;
    }

    /**
     * @param Builder          $builder
     * @param Libryo|null|null $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $builder, ?Libryo $libryo = null): Builder
    {
        $builder->typeLibryo()->whereNull('organisation_id');

        if ($libryo) {
            $builder->orWhere(function ($q) use ($libryo) {
                $q->typeLibryo()
                    ->whereRelation('organisation', 'id', $libryo->organisation_id);
            });
        }

        return $builder;
    }

    /**
     * Scope a query to only include folders/files of type global.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeForParent(Builder $query, ?int $parentId = null): Builder
    {
        if (is_null($parentId)) {
            /** @var Builder */
            return $query->whereNull('folder_parent_id');
        }

        return $query->where('folder_parent_id', $parentId);
    }

    /**
     * @param Builder $query
     * @param int     $organisationId
     *
     * @return Builder
     */
    public function scopeEmptyForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where(function ($q) use ($organisationId) {
            $q->whereDoesntHave('files.organisation', function ($q) use ($organisationId) {
                $q->whereKey($organisationId);
            });
            $q->doesntHave('children');
        });
    }

    /**
     * @param Builder $query
     * @param int     $organisationId
     *
     * @return Builder
     */
    public function scopeNotEmptyForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where(function ($q) use ($organisationId) {
            $q->whereRelation('files.organisation', 'id', $organisationId);
            $q->orHas('children');
        });
    }

    /**
     * @param Builder $query
     * @param int     $libryoId
     *
     * @return Builder
     */
    public function scopeEmptyForLibryo(Builder $query, int $libryoId): Builder
    {
        return $query->where(function ($q) use ($libryoId) {
            $q->whereDoesntHave('files.libryo', function ($q) use ($libryoId) {
                $q->whereKey($libryoId);
            });
            $q->doesntHave('children');
        });
    }

    /**
     * @param Builder $query
     * @param int     $libryoId
     *
     * @return Builder
     */
    public function scopeNotEmptyForLibryo(Builder $query, int $libryoId): Builder
    {
        return $query->where(function ($q) use ($libryoId) {
            $q->whereRelation('files.libryo', 'id', $libryoId);
            $q->orHas('children');
        });
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeEmpty(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->doesntHave('files');
            $q->doesntHave('children');
        });
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeNotEmpty(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->has('files');
            $q->orHas('children');
        });
    }

    /**
     * @param Builder     $query
     * @param Libryo|null $libryo
     *
     * @return Builder
     */
    public function scopeInGlobalDrive(Builder $query, ?Libryo $libryo): Builder
    {
        if (!is_null($libryo)) {
            if ($libryo->location) {
                $query->limitedLocationAncestors($libryo->location);
            } else {
                $query->doesntHave('locations');
            }
            $query->limitedLegalDomains($libryo->legalDomains);
        } else {
            $query->doesntHave('locations');
            $query->doesntHave('legalDomains');
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Returns the name of the folder type.
     *
     * @return string
     **/
    public function typeToName(): string
    {
        if ($this->isOrganisation()) {
            return 'organisation';
        }
        if ($this->isLibryo()) {
            return 'place';
        }
        if ($this->isGlobal()) {
            return 'global';
        }

        return 'system';
    }

    /**
     * @param int $entityId
     *
     * @return string
     */
    public function getStoragePath(?int $entityId = null)
    {
        $entityType = $this->typeToName();
        $entityIdPath = '';

        // makes sure the path is in the correct format if no entity id is provided
        if ($entityId) {
            $entityIdPath = '/' . $entityId;
        }

        $type = FileType::my();

        return config("filesystems.filetypes.{$type->value}.path") . '/' . $entityType . $entityIdPath;
    }

    /**
     * @param array<string, mixed> $folderSettings
     *
     * @return bool
     */
    public function isHiddenInSettings(array $folderSettings): bool
    {
        $key = 'folder_' . $this->id;
        if (!isset($folderSettings[$key])) {
            return false;
        }

        return isset($folderSettings[$key]['hidden']) && $folderSettings[$key]['hidden'];
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
