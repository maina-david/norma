<?php

namespace App\Models\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property Team $team
 *
 * @mixin IdeHelperProfile
 */
class Profile extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'librarian_profiles';

    /** @var array<string, string> */
    protected $casts = [
        'contract_signed' => 'bool',
        'restricted_visa' => 'bool',
        'skills_develop' => 'array',
        'start_date' => 'date',
        'residency_confirmed_at' => 'date',
        'is_team_admin' => 'bool',
        'allows_communication' => 'bool',
    ];

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
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CollaboratorApplication::class, 'turk_application_id');
    }

    /**
     * @return BelongsTo
     */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * @return HasMany
     */
    public function languages(): HasMany
    {
        return $this->hasMany(ProfileLanguage::class);
    }

    /**
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProfileDocument::class);
    }

    /**
     * @return HasMany
     */
    public function inactiveDocuments(): HasMany
    {
        return $this->documents()->where('active', false);
    }

    /**
     * @return HasOne
     */
    public function visa(): HasOne
    {
        return $this->hasOne(ProfileDocument::class)
            ->where('type', DocumentType::VISA->value)
            ->where('active', true);
    }

    /**
     * @return HasOne
     */
    public function identification(): HasOne
    {
        return $this->hasOne(ProfileDocument::class)
            ->where('type', DocumentType::IDENTIFICATION->value)
            ->where('active', true);
    }

    /**
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(ProfileDocument::class)
            ->where('type', DocumentType::CONTRACT->value)
            ->where('active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('is_team_admin', true);
    }

    /**
     * Add the query scope for the active document.
     *
     * @param Builder      $builder
     * @param DocumentType $type
     *
     * @return Builder
     */
    public function scopeWithDocumentType(Builder $builder, DocumentType $type): Builder
    {
        return $builder->with(['documents' => function ($query) use ($type) {
            $query->active()->ofType($type);
        }]);
    }

    /**
     * Scope to filter profiles having the document.
     *
     * @param Builder         $builder
     * @param DocumentType    $type
     * @param string|int|null $subtype
     *
     * @return Builder
     */
    public function scopeHasDocumentOf(Builder $builder, DocumentType $type, string|int|null $subtype = null): Builder
    {
        return $builder->whereHas('documents', fn ($query) => $query->ofType($type, $subtype));
    }

    /**
     * Filter by expired documents.
     *
     * @param Builder      $builder
     * @param DocumentType $type
     *
     * @return Builder
     */
    public function scopeExpiredDocument(Builder $builder, DocumentType $type): Builder
    {
        return $builder->whereHas('documents', fn ($query) => $query->ofType($type)->expired());
    }

    /**
     * Filter by valid documents.
     *
     * @param Builder      $builder
     * @param DocumentType $type
     *
     * @return Builder
     */
    public function scopeValidDocument(Builder $builder, DocumentType $type): Builder
    {
        return $builder->whereHas('documents', fn ($query) => $query->ofType($type)->valid());
    }

    /**
     * Filter by missing or valid documents.
     *
     * @param Builder      $builder
     * @param DocumentType $type
     *
     * @return Builder
     */
    public function scopeMissingOrValidDocument(Builder $builder, DocumentType $type): Builder
    {
        return $builder->where(function ($query) use ($type) {
            $query->validDocument($type)
                ->orWhereDoesntHave('documents', fn ($builder) => $builder->ofType($type)->active());
        });
    }

    /**
     * Filter by pending documents.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeWithPendingDocuments(Builder $builder): Builder
    {
        return $builder->with([
            'documents' => function ($query) {
                $query->whereIn('type', [DocumentType::VISA->value, DocumentType::IDENTIFICATION->value])
                    ->whereNull('approved_at')
                    ->where('expired', false);
            },
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the profile should re-verify the residency.
     *
     * @return bool
     */
    public function shouldReVerifyResidency(): bool
    {
        return !$this->residency_confirmed_at || now()->subMonths(6)->endOfDay()->gt($this->residency_confirmed_at);
    }
}
