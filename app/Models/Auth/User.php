<?php

namespace App\Models\Auth;

use App\Enums\Application\ApplicationType;
use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Enums\Notify\UserLegalUpdateSentStatus;
use App\Enums\Tasks\TaskType;
use App\Http\ModelFilters\Auth\UserFilter;
use App\Http\Services\Hubspot\Actions\UserToHubspotContact;
use App\Http\Services\Hubspot\Concerns\AsHubspotContact;
use App\Http\Services\Hubspot\Concerns\HubspotContact;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaUser;
use App\Models\Customer\Pivots\OrganisationUser;
use App\Models\Customer\Pivots\TeamUser;
use App\Models\Customer\Team;
use App\Models\Log\UserLifecycleActivity;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Notification;
use App\Models\Notify\Pivots\LegalUpdateUser;
use App\Models\Ontology\LegalDomain;
use App\Models\Partners\Partner;
use App\Models\Storage\My\Attachment;
use App\Models\Tasks\Pivots\TaskWatcher;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use App\Models\Traits\EncodesHashId;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Tasks\TaskNotificationService;
use App\Traits\HasProfilePhoto;
use App\Traits\HasSettings;
use DateTimeInterface;
use EloquentFilter\Filterable;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property \App\Models\Notify\Pivots\LegalUpdateUser $pivot
 * @property \App\Models\Auth\UserRole[]               $user_roles
 *
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements HubspotContact, Auditable
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasSettings;
    use HasProfilePhoto;
    use SoftDeletes;
    use Filterable;
    use HasApiTokens;
    use Searchable;
    use AsHubspotContact;
    use EncodesHashId;
    use \OwenIt\Auditing\Auditable;

    /** @var string[] */
    protected $guarded = ['id'];

    /** @var array<int, string>|null */
    protected ?array $computedPermissions = null;

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array<int, string>
     */
    protected array $auditExclude = [
        'start_place_id',
        'timezone',
        'language',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'bool',
        'email_daily' => 'bool',
        'email_monthly' => 'bool',
        'password_reset' => 'bool',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'profile_photo_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get all the permissions for the given user.
     *
     * @return array<string>
     */
    public function getPermissionsAttribute(): array
    {
        if (!$this->computedPermissions) {
            $this->computedPermissions = $this->roles
                ->filter(function ($role) {
                    /** @var Role $role */
                    return in_array($role->app, ApplicationType::validTypes());
                })
                ->map(function ($role) {
                    /** @var Role $role */
                    return collect(array_values(is_array($role->permissions) ? $role->permissions : []))
                        ->map(fn ($perm) => "{$role->app}.{$perm}")
                        ->toArray();
                })
                ->reduce(fn ($prev, $curr) => $prev->merge($curr), collect())
                ->unique()
                ->toArray();
        }

        return $this->computedPermissions;
    }

    /**
     * Get the full name attribute.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->fname} {$this->sname}";
    }

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->using(UserRole::class);
    }

    /**
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class, 'user_id')
            ->orderBy('id', 'DESC');
    }

    /**
     * @return BelongsToMany
     */
    public function normas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new NormaUser())->getTable(), 'user_id', 'place_id')
            ->using(NormaUser::class)
            ->withPivot(['is_admin']);
    }

    /**
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, (new TeamUser())->getTable())
            ->using(TeamUser::class);
    }

    /**
     * @return BelongsToMany
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, (new OrganisationUser())->getTable())
            ->using(OrganisationUser::class)
            ->withPivot(['is_admin']);
    }

    /**
     * @return HasMany
     */
    public function lifecycleActivities(): HasMany
    {
        return $this->hasMany(UserLifecycleActivity::class, 'user_id')
            ->orderBy('id', 'DESC');
    }

    /**
     * @return BelongsToMany
     */
    public function legalUpdates()
    {
        return $this->belongsToMany(LegalUpdate::class, (new LegalUpdateUser())->getTable(), 'user_id', 'register_notification_id')
            ->using(LegalUpdateUser::class)
            ->withTimestamps()
            ->withPivot(['read_status', 'understood_status', 'email_sent']);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function unreadLegalUpdates(): BelongsToMany
    {
        return $this->legalUpdates()
            ->wherePivot('read_status', false);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function readLegalUpdates(): BelongsToMany
    {
        return $this->legalUpdates()
            ->wherePivot('read_status', true);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function readUnderstoodLegalUpdates(): BelongsToMany
    {
        return $this->legalUpdates()
            ->wherePivot('read_status', true)
            ->wherePivot('understood_status', true);
    }

    /**
     * Notifications that the user has not received via email yet.
     *
     * @return BelongsToMany
     */
    public function unsentLegalUpdates(): BelongsToMany
    {
        return $this->legalUpdates()->wherePivot('email_sent', UserLegalUpdateSentStatus::WAITING->value);
    }

    /**
     * Notifications that the user has not received via email yet and can be sent.
     *
     * @return BelongsToMany
     */
    public function dispatchableLegalUpdates(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->unsentLegalUpdates()->dispatchable();
    }

    /**
     * Notifications that the user has received in the last month.
     *
     * @return BelongsToMany
     */
    public function lastMonthNotifications(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->legalUpdates()
            ->where(function ($builder) {
                $startOfMonth = now('UTC')->subMonth()->startOfMonth();
                $endOfMonth = now('UTC')->subMonth()->endOfMonth();

                $builder->whereBetween('release_at', [$startOfMonth, $endOfMonth]);
                $builder->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                    $table = (new LegalUpdate())->getTable();

                    $q->whereNull('release_at');
                    $q->whereBetween("{$table}.created_at", [$startOfMonth, $endOfMonth]);
                });
            });
    }

    /**
     * @return BelongsTo
     */
    public function avatarAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'avatar_attachment_id');
    }

    /**
     * @return HasMany
     */
    public function authoredTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'author_id');
    }

    /**
     * @return HasMany
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_id');
    }

    /**
     * @return BelongsToMany
     */
    public function watchingTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, (new TaskWatcher())->getTable(), 'user_id', 'task_id')
            ->using(TaskWatcher::class);
    }

    /**
     * @return HasMany
     */
    public function taskActivities(): HasMany
    {
        return $this->hasMany(TaskActivity::class, 'user_id');
    }

    /**
     * Have to override the Notifiable method, as we have legacy notifiable names. Once those have been
     * renamed, we can remove this.
     *
     * @return MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * @return HasOne
     */
    public function trial(): HasOne
    {
        return $this->hasOne(UserTrial::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get the query for the users with emails that can be sent.
     *
     * @param Builder            $builder
     * @param array<int, string> $updateFields
     * @param bool               $emailDaily
     *
     * @return Builder
     */
    public function scopeWithDispatchableMails(
        Builder $builder,
        array $updateFields = [],
        bool $emailDaily = false
    ): Builder {
        $updateFields = implode(',', $updateFields);
        $updateFields = empty($updateFields) ? '' : ":{$updateFields}";

        $query = $builder->active()
            ->whereNotNull('email')
            ->has('dispatchableLegalUpdates')
            ->with(["dispatchableLegalUpdates{$updateFields}"]);

        if ($emailDaily) {
            $query->where('email_daily', true);
        }

        return $query;
    }

    /**
     * Gets all users that received notifications in the last month.
     *
     * @param Builder            $builder
     * @param array<int, string> $updateFields
     * @param bool               $emailMonthly
     *
     * @return Builder
     */
    public function scopeNotifiedLastMonth(
        Builder $builder,
        array $updateFields = [],
        bool $emailMonthly = false
    ): Builder {
        $updateFields = implode(',', $updateFields);
        $updateFields = empty($updateFields) ? '' : ":{$updateFields}";

        $query = $builder->active()
            ->whereNotNull('email')
            ->has('lastMonthNotifications')
            ->with(["lastMonthNotifications{$updateFields}"]);

        if ($emailMonthly) {
            $query->where('email_monthly', true);
        }

        return $query;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('active', true);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeInActive(Builder $builder): Builder
    {
        return $builder->where('active', false);
    }

    /**
     * @param Builder $builder
     * @param string  $email
     *
     * @return Builder
     */
    public function scopeEmailLike(Builder $builder, string $email): Builder
    {
        return $builder->where('email', 'LIKE', "%{$email}%");
    }

    /**
     * @param Builder $builder
     * @param string  $name
     *
     * @return Builder
     */
    public function scopeNameLike(Builder $builder, string $name): Builder
    {
        return $builder->where(function ($q) use ($name) {
            $q->where('fname', 'LIKE', "%{$name}%")
                ->orWhere('sname', 'LIKE', "%{$name}%");
        });
    }

    /**
     * @param Builder        $builder
     * @param LifecycleStage $stage
     *
     * @return Builder
     */
    public function scopeInStage(Builder $builder, LifecycleStage $stage): Builder
    {
        return $builder->where('lifecycle_stage', $stage->value);
    }

    /**
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeNormaAccess(Builder $builder, Norma $norma): Builder
    {
        return $builder->where(function ($query) use ($norma) {
            $filter = function ($q) use ($norma) {
                $q->whereKey($norma->id);
            };
            $query->whereHas('teams.normas', $filter)
                ->orWhereHas('normas', $filter);
        });
    }

    /**
     * @param Builder $builder
     * @param int     $organisationId
     *
     * @return Builder
     */
    public function scopeInOrganisation(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereHas('organisations', function ($q) use ($organisationId) {
            $q->whereKey($organisationId);
        });
    }

    /**
     * @param Builder      $builder
     * @param TaskActivity $activity
     *
     * @return Builder
     */
    public function scopeShouldBeNotifiedOfTaskActivity(Builder $builder, TaskActivity $activity): Builder
    {
        return $builder->whereDoesntHave('taskActivities', function ($q) use ($activity) {
            $q->whereKey($activity->id); // the user who performed the activity shouldn't receive
        })
            ->where(function ($q) use ($activity) {
                // authors should be notified, but only if setting is not disabled
                $q->where(function ($q) use ($activity) {
                    $q->whereRelation('authoredTasks', 'id', $activity->task_id);
                    /** @var TaskNotificationService */
                    $service = app(TaskNotificationService::class);
                    $authorSetting = $service->getUserSettingForActivity($activity, 'author');
                    $q->where('settings->' . $authorSetting, '!=', false);
                });

                // authors should be notified, but only if setting is not disabled
                $q->orWhere(function ($q) use ($activity) {
                    $q->whereRelation('assignedTasks', 'id', $activity->task_id);
                    /** @var TaskNotificationService */
                    $service = app(TaskNotificationService::class);
                    $assigneeSetting = $service->getUserSettingForActivity($activity, 'assignee');
                    $q->where('settings->' . $assigneeSetting, '!=', false);
                });

                // authors should be notified, but only if setting is not disabled
                $q->orWhere(function ($q) use ($activity) {
                    $q->whereRelation('watchingTasks', 'id', $activity->task_id);
                    /** @var TaskNotificationService */
                    $service = app(TaskNotificationService::class);
                    $watcherSetting = $service->getUserSettingForActivity($activity, 'watcher');
                    $q->where('settings->' . $watcherSetting, '!=', false);
                });
            });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotIntegrationUser(Builder $builder): Builder
    {
        return $builder->whereDoesntHave('roles', fn ($q) => $q->where('title', 'Integration User'));
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeHubspotSyncable(Builder $builder): Builder
    {
        return $builder->whereNotNull('email')
            ->where(function ($query) {
                $query
                    ->where(function ($builder) {
                        $builder->where('active', true)
                            ->where('lifecycle_stage', '!=', LifecycleStage::deactivated()->value);
                    })
                    ->orHas('hubspot');
            })

            ->whereIn('user_type', [UserType::customer()->value, UserType::demo()->value, UserType::free()->value])
            ->whereHas('roles', function ($builder) {
                $builder->orWhere('title', 'My Users');
            })
            ->with(['roles:id,title']);
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
        return $this->provideFilter(UserFilter::class);
    }

    /**
     * Get the name to be used when creating the profile photo.
     *
     * @return string
     */
    protected function getProfileName(): string
    {
        return "{$this->fname} {$this->sname}";
    }

    /**
     * Flush the computed permissions so that we can re-compute.
     *
     * @return $this
     */
    public function flushComputedPermissions(): self
    {
        $this->computedPermissions = null;

        unset($this->roles);

        return $this;
    }

    /**
     * @return bool
     */
    public function isMySuperUser(): bool
    {
        return in_array('my.' . config('permissions.superuser-name'), $this->permissions);
    }

    /**
     * @return bool
     */
    public function isCollaborateSuperUser(): bool
    {
        return in_array('collaborate.' . config('permissions.superuser-name'), $this->permissions);
    }

    /**
     * @return bool
     */
    public function canManageAllOrganisations(): bool
    {
        return in_array('my.' . config('permissions.superuser-name'), $this->permissions)
            || $this->can('my.settings.customer.organisation.manage');
    }

    /**
     * Check if the user can manage applicability.
     *
     * @return bool
     */
    public function canManageApplicability(): bool
    {
        $manager = app(ActiveNormasManager::class);

        $singleStream = $manager->isSingleMode();

        return ($singleStream && $manager->getActive()?->isApplicabilityAccessible($this))
            || (!$singleStream && $manager->getActiveOrganisation()->isApplicabilityAccessible($this));
    }

    /**
     * Checks whether a user has access to partner.
     *
     * @param Partner $partner
     *
     * @return bool
     */
    public function hasPartnerAccess(Partner $partner): bool
    {
        return $this->partner_id === $partner->id;
    }

    /**
     * Checks whether the user is integration user.
     *
     * @return bool
     */
    public function isIntegrationUser(): bool
    {
        return $this->roles->contains('title', 'Integration User');
    }

    /**
     * Exclude the given permissions from the autogenerated CRUD permissions.
     *
     * @return array<string, array<int, string>>
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::admin()->value => [],
            ApplicationType::collaborate()->value => [],
            ApplicationType::my()->value => [],
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,mixed>
     */
    public function toSearchableArray()
    {
        $searchable = $this->toArray();

        unset($searchable['profile_photo_url']);

        return $searchable;
    }

    /**
     * Checks whether a user has access to the given norma.
     *
     * @param Norma $norma
     *
     * @return bool
     */
    public function hasNormaAccess(Norma $norma)
    {
        $builder = $norma->newQueryWithoutScopes()->whereKey($norma->id);
        $builder->userHasAccess($this);

        return $builder->exists();
    }

    /**
     * Checks whether a user is a member of an organisation.
     *
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function isInOrganisation(Organisation $organisation)
    {
        return $this->organisations->contains($organisation);
    }

    /**
     * Checks whether a user is an admin of an organisation.
     *
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function isOrganisationAdmin(Organisation $organisation): bool
    {
        return OrganisationUser::where('organisation_id', $organisation->id)
            ->where('user_id', $this->id)
            ->where('is_admin', true)
            ->exists();
    }

    /**
     * Check whether the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->roles()->where('title', 'Administrators')->exists(); // TODO: Switch to permissions.
    }

    /**
     * @return array<string>
     */
    public static function getSettingsAllowedToUpdate(): array
    {
        return config(sprintf('norma.model_settings.%s.allowed_to_update', static::class)) ?? [];
    }

    /**
     * @return array<string>
     */
    public static function getNotificationSettings(): array
    {
        return config(sprintf('norma.model_settings.%s.notification_settings', static::class)) ?? [];
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getTaskAppFilters(): array
    {
        $arr = $this->settings['app_filters']['tasks'] ?? [];

        $out = [];
        // legacy filters are still stored in the DB...this renames them
        foreach ($arr as $filter => $value) {
            if (is_null($value) || $value === false || (is_array($value) && empty($value)) || in_array($filter, ['normaOnly', 'following'])) {
                continue;
            }
            if ($filter === 'type' && TaskType::valueExists($value)) {
                $out['type'] = TaskType::toKey($value);
                continue;
            }
            if ($filter === 'status') {
                $out['statuses'] = $value;
                continue;
            }
            if ($filter === 'taskProject') {
                $out['project'] = $value;
                continue;
            }
            $out[$filter] = $value;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return void
     */
    public function updateTaskFilters(array $filters): void
    {
        $this->updateSetting('app_filters.tasks', $filters);
    }

    /**
     * Format the date to users timezone.
     *
     * @param Carbon|DateTimeInterface|string $date
     *
     * @return string
     */
    public function formatDate(Carbon|DateTimeInterface|string $date): string
    {
        return Carbon::parse($date)
            ->setTimezone($this->timezone ?? 'UTC')
            ->format('jS F Y, h:i A');
    }

    /**
     * @param string $date
     *
     * @return Carbon
     */
    public function toUserDate(string $date): Carbon
    {
        return Carbon::parse($date, $this->timezone ?? 'UTC');
    }

    /**
     * Validate if the current contact should sync to Hubspot.
     *
     * @return bool
     */
    public function shouldSyncToHubspot(): bool
    {
        $this->loadMissing(['roles', 'hubspot']);

        $active = ($this->active && $this->lifecycle_stage !== LifecycleStage::deactivated()->value)
            || ($this->hubspot->user_id ?? false);

        return !empty($this->email)
            && $active
            && $this->roles->where('title', 'My Users')->where('app', ApplicationType::my()->value)->isNotEmpty()
            && in_array($this->user_type, [
                UserType::customer()->value,
                UserType::demo()->value,
                UserType::free()->value,
            ]);
    }

    /**
     * Convert the object to a collection of hubspot contact properties.
     *
     * @return SimplePublicObjectInput
     */
    public function toHubspotContact(): SimplePublicObjectInput
    {
        return UserToHubspotContact::convert($this);
    }

    /**
     * Checks whether the user should receive the given notification.
     *
     * @param Collection<LegalDomain> $domains
     *
     * @return bool
     */
    public function shouldReceiveLegalUpdate(Collection $domains): bool
    {
        if (is_null($this->email) || $this->lifecycle_stage === LifecycleStage::deactivated()->value || !$this->active) {
            return false;
        }

        $legalDomainArr = $domains->pluck('id')->toArray();

        if (empty($legalDomainArr) || ($this->settings['all_legal_domains'] ?? true)) {
            return true;
        }

        $subscribed = $this->subscribedLegalDomainIds();

        return !empty($subscribed) && (count(array_intersect($subscribed, $legalDomainArr)) > 0);
    }

    /**
     * Get the legal domains that the user has subscribed to.
     *
     * @return array<int, int>
     */
    public function subscribedLegalDomainIds(): array
    {
        if (isset($this->settings['all_legal_domains']) && $this->settings['all_legal_domains'] === true) {
            return [];
        }

        return $this->settings['notification_legal_domains'] ?? [];
    }

    /**
     * Should the user receive an email when a reminder is sent.
     *
     * @return bool
     */
    public function shouldReceiveReminderEmail(): bool
    {
        return $this->settings['email_reminder'] ?? true;
    }

    /**
     * Should the user recieve an email when mentioned in a comment.
     *
     * @return bool
     */
    public function shouldReceiveMentionEmail(): bool
    {
        return $this->settings['email_comment_mention'] ?? true;
    }

    /**
     * Should the user recieve an email when comment is replied to.
     *
     * @return bool
     */
    public function shouldReceiveReplyEmail(): bool
    {
        return $this->settings['email_comment_reply'] ?? true;
    }

    /**
     * Check whether the user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    /**
     * Checks whether the user has been invited to the platform.
     *
     * @return bool
     */
    public function hasBeenInvited(): bool
    {
        return LifecycleStage::invitationSent()->is($this->lifecycle_stage);
    }

    /**
     * Checks whether the user has failed to accept the invite email.
     *
     * @return bool
     */
    public function declinedInvitation(): bool
    {
        return LifecycleStage::invitationDeclined()->is($this->lifecycle_stage);
    }

    /**
     * Update collaborate roles.
     *
     * @param array<array-key, int> $newRoles
     *
     * @return $this
     */
    public function updateCollaborateRoles(array $newRoles): self
    {
        return $this->syncAppRoles(ApplicationType::collaborate(), $newRoles);
    }

    /**
     * Update my roles.
     *
     * @param array<array-key, int> $newRoles
     *
     * @return $this
     */
    public function updateMyRoles(array $newRoles): self
    {
        return $this->syncAppRoles(ApplicationType::my(), $newRoles);
    }

    /**
     * Update the app roles.
     *
     * @param ApplicationType       $app
     * @param array<array-key, int> $newRoles
     *
     * @return $this
     */
    protected function syncAppRoles(ApplicationType $app, array $newRoles): self
    {
        $existing = $this->roles()->where('app', $app->value)->pluck('id');

        $this->roles()->detach($existing->toArray());

        $this->roles()->attach($newRoles);

        return $this;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        if (!$this->identity_provider_id) {
            $this->notify(new ResetPasswordNotification($token));
        }
    }

    /**
     * @return void
     */
    public function anonymizeAndDestroyAccount(): void
    {
        $this->email = 'deleted' . $this->id . '@norma.com';
        $this->fname = 'Deleted';
        $this->sname = 'User';
        $this->password = Hash::make(Str::random());
        $this->email_daily = false;
        $this->email_monthly = false;
        $this->identity_provider_id = null;
        $this->identity_provider_name_id = null;
        $this->phone_home = null;
        $this->phone_mobile = null;
        $this->mobile_country_code = null;
        $this->date_of_birth = null;
        $this->gender = null;
        $this->id_number = null;
        $this->addr_physical = null;
        $this->addr_postal = null;
        $this->remember_token = null;

        $this->save();

        $this->delete();
    }
}
