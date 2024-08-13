<?php

namespace App\Models\Storage\My;

use App\Enums\Storage\FileType;
use App\Enums\Storage\My\FolderType;
use App\Http\ModelFilters\Storage\My\FileFilter;
use App\Models\AbstractModel;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Notify\Reminder;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\UserTag;
use App\Models\Storage\My\Pivots\AssessmentItemResponseFile;
use App\Models\Storage\My\Pivots\FileLegalDomain;
use App\Models\Storage\My\Pivots\FileLocation;
use App\Models\Storage\My\Pivots\FileTask;
use App\Models\Tasks\Task;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasFolderType;
use App\Models\Traits\HasFulltextSearchScope;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Traits\LimitsLegalDomains;
use App\Models\Traits\LimitsLocations;
use App\Services\Storage\FileHelper;
use App\Services\Storage\MimeTypeManager;
use App\Traits\InteractsWithStorage;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperFile
 */
class File extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use HasFolderType;
    use InteractsWithStorage;
    use LimitsLocations;
    use LimitsLegalDomains;
    use Filterable;
    use HasTitleLikeScope;
    use HasComments;
    use HasFulltextSearchScope;
    use HasTitleLikeScope;
    use \OwenIt\Auditing\Auditable;

    /** @var array<string, string> */
    protected $casts = [
        'show_preview' => 'bool',
        'replaced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'date_last_accessed' => 'datetime',
    ];

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
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return BelongsTo
     */
    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class, 'place_id');
    }

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new FileLocation())->getTable(), 'file_id', 'location_id')
            ->using(FileLocation::class);
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new FileLegalDomain())->getTable(), 'file_id', 'legal_domain_id')
            ->using(FileLegalDomain::class);
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return MorphToMany
     */
    public function userTags(): MorphToMany
    {
        return $this->morphToMany(UserTag::class, 'user_taggable');
    }

    /**
     * @return MorphMany
     */
    public function taskableTasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * @return BelongsToMany
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, (new FileTask())->getTable(), 'file_id', 'task_id')
            ->using(FileTask::class);
    }

    /**
     * @return BelongsToMany
     */
    public function assessmentItemResponses(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentItemResponse::class, (new AssessmentItemResponseFile())->getTable(), 'file_id', 'assessment_item_response_id')
            ->using(AssessmentItemResponseFile::class);
    }

    /**
     * @return MorphMany
     */
    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $query
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $query, Libryo $libryo): Builder
    {
        return $query->typeLibryo()
            ->whereRelation('libryo', 'id', $libryo->id);
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $query, Organisation $organisation): Builder
    {
        return $query->typeOrganisation()
            ->whereRelation('organisation', 'id', $organisation->id);
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeAllForOrganisation(Builder $query, Organisation $organisation): Builder
    {
        $libryoIds = $organisation->libryos()->pluck('id');

        return $query->where(function ($q) use ($libryoIds, $organisation) {
            $q->whereRelation('organisation', 'id', $organisation->id)
                ->orWhereHas('libryo', function ($q) use ($libryoIds) {
                    $q->whereKey($libryoIds);
                });
        });
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeAllForOrganisationUserAccess(Builder $query, Organisation $organisation, User $user): Builder
    {
        $libryoIds = $organisation->libryos()->userHasAccess($user)->pluck('id');

        return $query->where(function ($q) use ($libryoIds, $organisation) {
            $q->whereRelation('organisation', 'id', $organisation->id)
                ->orWhereHas('libryo', function ($q) use ($libryoIds) {
                    $q->whereKey($libryoIds);
                });
        });
    }

    /**
     * @param Builder     $query
     * @param Folder|null $folder
     * @param Libryo|null $libryo
     *
     * @return Builder
     */
    public function scopeInGlobalDrive(Builder $query, ?Folder $folder, ?Libryo $libryo): Builder
    {
        $query->typeGlobal();

        if (!is_null($folder)) {
            $query->where('folder_id', $folder->id);
        }

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

    /**
     * @param Builder $builder
     * @param string  $title
     *
     * @return Builder
     */
    public function scopeExtensionLike(Builder $builder, string $title): Builder
    {
        return $builder->where('extension', 'LIKE', "%{$title}%");
    }

    /**
     * Scope to add permissions for managing global, org and libryo files.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeWithPermissions(Builder $builder, User $user): Builder
    {
        $builder->withCasts([
            'can_manage_global' => 'bool',
            'can_manage_org' => 'bool',
            'author_id' => 'bool',
        ]);

        if (!$builder->getQuery()->columns) {
            $builder->select();
        }

        $builder->addSelect(DB::raw("author_id = {$user->id} as is_author"));

        if ($user->isMySuperUser()) {
            $builder->addSelect([
                'can_manage_global' => DB::raw('1 as can_manage_global'),
                'can_manage_org' => DB::raw('1 as can_manage_org'),
            ]);

            return $builder;
        }

        $builder->addSelect([
            'can_manage_global' => DB::raw('0 as can_manage_global'),
            'can_manage_org' => Organisation::select(DB::raw(1))
                ->userIsOrganisationAdmin($user)
                ->where(function ($query) {
                    $query
                        ->whereColumn(
                            sprintf('%s.organisation_id', $this->getTable()),
                            sprintf('%s.id', (new Organisation())->getTable())
                        )
                        ->orWhereHas('libryos', function ($builder) {
                            $builder->whereColumn(
                                sprintf('%s.place_id', $this->getTable()),
                                sprintf('%s.id', (new Libryo())->getTable()),
                            );
                        });
                }),
        ]);

        return $builder;
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
        return $this->provideFilter(FileFilter::class);
    }

    /**
     * Get the path to the item in storage.
     *
     * @return string
     */
    protected function filePath(): string
    {
        return $this->path;
    }

    /**
     * Get the name of the file.
     *
     * @return string
     */
    protected function fileName(): string
    {
        return $this->title;
    }

    /**
     * Get the mime type of the file.
     *
     * @return string
     */
    protected function fileMimeType(): string
    {
        return $this->mime_type;
    }

    /**
     * Handle the changes required after the file has been saved in storage.
     *
     * @param UploadedFile $file
     * @param string       $path
     *
     * @return void
     */
    protected function fileSaved(UploadedFile $file, string $path): void
    {
    }

    /**
     * Get the mime type of the file.
     *
     * @return FileType
     */
    protected function getFileType(): FileType
    {
        return FileType::my();
    }

    /**
     * Get a human readable string for the file size that's stored in bytes.
     *
     * @return string
     */
    public function getHumanReadableSize(int $decimals = 1): string
    {
        return app(FileHelper::class)->getHumanReadableSize($this->size, $decimals);
    }

    /**
     * @return bool
     */
    public function isPreviewable(): bool
    {
        $manager = app(MimeTypeManager::class);

        return $manager->isImage($this->mime_type) || $manager->isPdf($this->mime_type);
    }

    public function isGlobal(): bool
    {
        return FolderType::global()->is($this->folder_type);
    }

    public function isTypeLibryo(): bool
    {
        return FolderType::libryo()->is($this->folder_type);
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
            'description' => $this->description,
        ];
    }
}
