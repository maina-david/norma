<?php

namespace App\Models\Customer;

use App\Enums\System\LibryoModule;
use App\Http\ModelFilters\Customer\OrganisationFilter;
use App\Models\AbstractModel;
use App\Models\Auth\IdentityProvider;
use App\Models\Auth\User;
use App\Models\Customer\Pivots\OrganisationUser;
use App\Models\Partners\Partner;
use App\Models\Partners\WhiteLabel;
use App\Models\Storage\My\Folder;
use App\Models\Tasks\TaskProject;
use App\Models\Traits\HasTitleLikeScope;
use App\Traits\HasSettings;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperOrganisation
 */
class Organisation extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use HasSettings;
    use Filterable;
    use HasTitleLikeScope;
    use \OwenIt\Auditing\Auditable;

    /** @var array<string, string> */
    protected $casts = [
        'settings' => 'array',
        'translation_enabled' => 'bool',
        'is_parent' => 'bool',
        'identity_provider_token' => 'encrypted',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasOne
     */
    public function identityProvider(): HasOne
    {
        return $this->hasOne(IdentityProvider::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, (new OrganisationUser())->getTable())
            ->using(OrganisationUser::class)
            ->withPivot(['is_admin']);
    }

    /**
     * @return BelongsToMany
     */
    public function adminUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('is_admin', true);
    }

    /**
     * @return HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * @return HasMany
     */
    public function libryos(): HasMany
    {
        return $this->hasMany(Libryo::class);
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * @return BelongsTo
     */
    public function whitelabel(): BelongsTo
    {
        return $this->belongsTo(WhiteLabel::class);
    }

    /**
     * @return HasMany
     */
    public function taskProjects(): HasMany
    {
        return $this->hasMany(TaskProject::class);
    }
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeUserIsOrganisationAdmin(Builder $builder, User $user): Builder
    {
        return $builder->whereRelation('adminUsers', 'id', $user->id);
    }

    /**
     * Organisations that the given user has access to via libryo streams.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeUserHasAccess(Builder $builder, User $user): Builder
    {
        return $builder->whereHas('libryos', function ($q) use ($user) {
            $q->userHasAccess($user);
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
        return $this->provideFilter(OrganisationFilter::class);
    }

    /**
     * Get the total storage allocated to the organisation (in gigabytes).
     *
     * @return int
     */
    public function getStorageAllocation(): int
    {
        return (int) ($this->settings['storage_allocation'] ?? static::defaultSettings()['storage_allocation']);
    }

    /**
     * Check whether this organisation has the module enabled.
     *
     * @return bool
     */
    public function hasModule(string $module): bool
    {
        return $this->settings['modules'][$module] ?? false;
    }

    /**
     * Check whether this organisation has the live chat module enabled.
     *
     * @return bool
     */
    public function hasLiveChat(): bool
    {
        return $this->hasModule('live_chat');
    }

    /**
     * Check whether this organisation has the tasks module enabled.
     *
     * @return bool
     */
    public function hasTaskModule(): bool
    {
        return $this->hasModule('tasks');
    }

    /**
     * Check whether this organisation has the actions module enabled.
     *
     * @return bool
     */
    public function hasActionsModule(): bool
    {
        return $this->hasModule('actions');
    }

    /**
     * Check whether this organisation has the comply module enabled.
     *
     * @return bool
     */
    public function hasAssessModule(): bool
    {
        return $this->hasModule('comply');
    }

    /**
     * Check whether this organisation has SSO module.
     *
     * @return bool
     */
    public function hasSSOModule(): bool
    {
        return $this->hasModule(LibryoModule::sso()->value);
    }

    /**
     * Enables the given module for this org.
     *
     * @param LibryoModule $module
     *
     * @return bool
     */
    public function enableModule(LibryoModule $module): bool
    {
        return $this->updateSetting('modules.' . $module->value, true);
    }

    /**
     * @param Folder $folder
     *
     * @return self
     */
    public function hideFolderInSettings(Folder $folder): self
    {
        $folderSettings = $this->settings['folders'];
        if (!isset($folderSettings['folder_' . $folder->id])) {
            $folderSettings['folder_' . $folder->id] = [];
        }
        $folderSettings['folder_' . $folder->id]['hidden'] = true;
        $this->updateSetting('folders', $folderSettings);

        return $this;
    }

    /**
     * @param Folder $folder
     *
     * @return self
     */
    public function showFolderInSettings(Folder $folder): self
    {
        $folderSettings = $this->settings['folders'];
        if (!isset($folderSettings['folder_' . $folder->id]) || !isset($folderSettings['folder_' . $folder->id]['hidden'])) {
            return $this;
        }
        unset($folderSettings['folder_' . $folder->id]['hidden']);

        $this->updateSetting('folders', $folderSettings);

        return $this;
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
     * Set the slug for the organisation.
     *
     * @return $this
     */
    public function setSlug(): self
    {
        if (!$this->slug) {
            $this->slug = Str::slug($this->title);
            $count = Organisation::where('slug', $this->slug)->count();
            $this->slug = $count > 0 ? "{$this->slug}-{$count}" : $this->slug;
        }

        return $this;
    }

    /**
     * Check if the module is enabled but hidden from non-super users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isApplicabilityAccessible(User $user): bool
    {
        $autoCompiled = $this->libryos()->where('auto_compiled', true)->exists();
        $hidden = $this->libryos()->where('settings->modules->hide_applicability', true)->exists();

        return $autoCompiled && (!$hidden || $user->isMySuperUser());
    }
}
