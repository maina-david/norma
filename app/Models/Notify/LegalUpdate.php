<?php

namespace App\Models\Notify;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Enums\Notify\LegalUpdateStatus;
use App\Http\ModelFilters\Notify\LegalUpdateFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Bookmarks\LegalUpdateBookmark;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Library;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Notify\Pivots\ContextQuestionLegalUpdate;
use App\Models\Notify\Pivots\LegalDomainLegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateLibrary;
use App\Models\Notify\Pivots\LegalUpdateNorma;
use App\Models\Notify\Pivots\LegalUpdateUser;
use App\Models\Notify\Pivots\LegalUpdateWork;
use App\Models\Ontology\LegalDomain;
use App\Models\Tasks\Task;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\EncodesHashId;
use App\Models\Traits\HasComments;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property \App\Models\Corpus\Work|null              $work
 * @property \App\Models\Notify\Pivots\LegalUpdateUser $pivot
 *
 * @mixin IdeHelperLegalUpdate
 */
class LegalUpdate extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use Filterable;
    use HasTitleLikeScope;
    use HasComments;
    use ApiQueryFilterable;
    use EncodesHashId;
    use \OwenIt\Auditing\Auditable;

    /** @var string */
    protected $table = 'corpus_legal_updates';

    /** @var array<string, string> */
    protected $casts = [
        'effective_date' => 'date',
        'publication_date' => 'date',
        'release_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'late' => 'boolean',
    ];

    /** @var int|null */
    public ?int $first_applicable_place_id = null;

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * @return bool|null
     */
    public function getUserReadStatusAttribute(): ?bool
    {
        /** @var User */
        $user = Auth::user();
        $thisUser = $this->users->firstWhere('id', $user->id);

        return $thisUser?->pivot?->read_status ?? null;
    }

    /**
     * @return bool|null
     */
    public function getUserUnderstoodStatusAttribute(): ?bool
    {
        /** @var User */
        $user = Auth::user();
        $thisUser = $this->users->firstWhere('id', $user->id);

        return $thisUser?->pivot?->understood_status ?? null;
    }

    /**
     * If a release_at date is set, use that, otherwise use created_at date.
     *
     * @return Carbon
     **/
    public function getNotificationDateAttribute(): Carbon
    {
        /** @var Carbon */
        return $this->release_at ?? $this->created_at;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maintainedWorks(): BelongsToMany
    {
        return $this->belongsToMany(Work::class, (new LegalUpdateWork())->getTable())
            ->using(LegalUpdateWork::class);
    }

    /**
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * @return BelongsToMany
     */
    public function libraries(): BelongsToMany
    {
        return $this->belongsToMany(Library::class, (new LegalUpdateLibrary())->getTable(), 'register_notification_id', 'library_id')
            ->using(LegalUpdateLibrary::class);
    }

    /**
     * @return BelongsToMany
     */
    public function normas(): BelongsToMany
    {
        return $this->belongsToMany(Norma::class, (new LegalUpdateNorma())->getTable(), 'register_notification_id', 'place_id')
            ->using(LegalUpdateNorma::class);
    }

    /**
     * NB!! Don't use this for anything - it's just here for a legacy API endpoint used by a partner (legal-updates/filtered?count=places).
     *
     * @return BelongsToMany
     */
    public function places(): BelongsToMany
    {
        return $this->normas();
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, (new LegalUpdateUser())->getTable(), 'register_notification_id', 'user_id')
            ->using(LegalUpdateUser::class)
            ->withTimestamps()
            ->withPivot(['read_status', 'understood_status', 'email_sent']);
    }

    /**
     * @return BelongsTo
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function unreadUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('read_status', false);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function readUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('read_status', true);
    }

    /**
     * Legal updates that the user has not opened yet.
     *
     * @return BelongsToMany
     */
    public function readUnderstoodUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('read_status', true)
            ->wherePivot('understood_status', true);
    }

    /**
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * @return BelongsTo
     */
    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'in_terms_of_work_id');
    }

    /**
     * @return BelongsTo
     */
    public function notifyReference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'notify_reference_id');
    }

    /**
     * @return HasOne
     */
    public function notified(): HasOne
    {
        return $this->hasOne(LegalUpdateNotified::class, 'register_notification_id');
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new LegalDomainLegalUpdate())->getTable(), 'legal_update_id', 'legal_domain_id')
            ->using(LegalDomainLegalUpdate::class);
    }

    /**
     * @return BelongsTo
     */
    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    /**
     * @return BelongsTo
     */
    public function contentResource(): BelongsTo
    {
        return $this->belongsTo(ContentResource::class, 'content_resource_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdFromDoc(): BelongsTo
    {
        return $this->belongsTo(Doc::class, 'created_from_doc_id');
    }

    public function contextQuestions(): BelongsToMany
    {
        return $this->belongsToMany(ContextQuestion::class, (new ContextQuestionLegalUpdate())->getTable(), 'legal_update_id', 'context_question_id')
            ->using(ContextQuestionLegalUpdate::class);
    }

    /**
     * @return HasMany
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(LegalUpdateBookmark::class, 'legal_update_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDispatchable(Builder $builder): Builder
    {
        return $builder->where(function ($query) {
            $query->whereBetween('release_at', [now('UTC')->subMonth(), now('UTC')])
                ->orWhere(function ($builder) {
                    $table = $this->getTable();

                    $builder->whereNull('release_at');
                    $builder->whereBetween("{$table}.created_at", [
                        now('UTC')->subMonth(),
                        now('UTC'),
                    ]);
                });
        });
    }

    /**
     * @param Builder $query
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearchLike(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->titleLike($search);
            $q->orWhere('title_translation', 'like', "%{$search}%");
            $q->orWhereRelation('work', 'highlights', 'like', '%' . $search . '%');
        });
    }

    /**
     * @param Builder           $query
     * @param User              $user
     * @param LegalUpdateStatus $status
     *
     * @return Builder
     */
    public function scopeUserStatus(Builder $query, User $user, LegalUpdateStatus $status): Builder
    {
        return $query->where(function ($q) use ($status, $user) {
            if (LegalUpdateStatus::unread()->is($status)) {
                $q->whereHas('unreadUsers', function ($q) use ($user) {
                    $q->whereKey($user->id);
                });
                $q->orWhereDoesntHave('users', function ($q) use ($user) {
                    $q->whereKey($user->id);
                });

                return;
            }
            if (LegalUpdateStatus::read()->is($status)) {
                $q->whereHas('readUsers', function ($q) use ($user) {
                    $q->whereKey($user->id);
                });

                return;
            }
            if (LegalUpdateStatus::readUnderstood()->is($status)) {
                $q->whereHas('readUnderstoodUsers', function ($q) use ($user) {
                    $q->whereKey($user->id);
                });
            }
        });
    }

    public function scopeUserStatusFromString(Builder $query, User $user, string $status): Builder
    {
        /** @var LegalUpdateStatus */
        $updateStatus = LegalUpdateStatus::fromValue((int) $status);

        return $query->userStatus($user, $updateStatus);
    }

    /**
     * @param Builder $query
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $query, Norma $norma): Builder
    {
        return $query->whereRelation('normas', 'id', $norma->id);
    }

    /**
     * @param Builder    $query
     * @param array<int> $normaIds
     *
     * @return Builder
     */
    public function scopeForNormas(Builder $query, array $normaIds): Builder
    {
        return $query->whereHas('normas', fn ($q) => $q->active()->whereKey($normaIds));
    }

    /**
     * @param Builder      $query
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $query, Organisation $organisation): Builder
    {
        return $query->whereRelation('normas.organisation', 'id', $organisation->id);
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
        return $query->whereRelation('normas', function ($q) use ($organisation, $user) {
            $q->active()->userHasAccess($user);
            $q->whereRelation('organisation', 'id', $organisation->id);
        });
    }

    /**
     * @param Builder $query
     * @param Carbon  $start
     * @param Carbon  $end
     *
     * @return Builder
     */
    public function scopeReleasedBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('release_at', '>=', $start)
            ->where('release_at', '<=', $end);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeReleasedThisMonth(Builder $query): Builder
    {
        return $query->where('release_at', '>=', Carbon::now()->startOfMonth())
            ->where('release_at', '<=', Carbon::now());
    }

    /**
     * @param Builder $query
     * @param Carbon  $date
     *
     * @return Builder
     */
    public function scopeSentAfter(Builder $query, Carbon $date): Builder
    {
        /** @var Builder */
        return $query->whereRaw('COALESCE(release_at,created_at) >= ?', [$date->format('Y-m-d')]);
    }

    /**
     * @param Builder $query
     * @param Carbon  $date
     *
     * @return Builder
     */
    public function scopeSentBefore(Builder $query, Carbon $date): Builder
    {
        /** @var Builder */
        return $query->whereRaw('COALESCE(release_at,created_at) <= ?', [$date->format('Y-m-d')]);
    }

    /**
     * @param Builder $query
     * @param Carbon  $startDate
     * @param Carbon  $endDate
     *
     * @return Builder
     */
    public function scopeSentBetween(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        /** @var Builder */
        return $query->whereRaw('COALESCE(release_at,created_at) BETWEEN "' . $startDate->format('Y-m-d') . '" AND "' . $endDate->format('Y-m-d') . '"');
    }

    /**
     * Get all notifications that haven't been notified yet.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUnnotifiedReadyForRelease(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->doesntHave('notified')
            ->has('notifyReference')
            ->whereNotNull('release_at')
            ->whereBetween('release_at', [now('UTC')->subMonth(), now('UTC')])
            ->whereNotNull('notify_reference_id')
            ->where('status', LegalUpdatePublishedStatus::PUBLISHED->value);
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
        return $this->provideFilter(LegalUpdateFilter::class);
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
            'title_translation' => $this->title_translation,
        ];
    }
}
