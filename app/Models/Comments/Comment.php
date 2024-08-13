<?php

namespace App\Models\Comments;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Traits\AttachedToLibryoAndOrganisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperComment
 */
class Comment extends AbstractModel
{
    use AttachedToLibryoAndOrganisation;

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class, 'place_id');
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the owning commentable models.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    // public function legalUpdate()
    // {
    //     return $this->commentable();
    // }

    /**
     * @return BelongsTo
     */
    // public function reference()
    // {
    //     return $this->commentable();
    // }

    /**
     * @return BelongsTo
     */
    // public function assessmentItem()
    // {
    //     return $this->commentable();
    // }

    /**
     * @return BelongsTo
     */
    // public function assessmentItemResponse()
    // {
    //     return $this->commentable();
    // }

    /**
     * @return BelongsTo
     */
    // public function file()
    // {
    //     return $this->commentable();
    // }

    /**
     * @return MorphMany
     */
    public function replies()
    {
        return $this->morphMany(self::class, 'commentable');
    }

    /**
     * @return MorphMany
     */
    public function comments()
    {
        return $this->replies();
    }

    /**
     * @return BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'comment_file', 'comment_id', 'file_id');
    }
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $builder, Libryo $libryo): Builder
    {
        return $builder->whereRelation('libryo', 'id', $libryo->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder
    {
        return $builder->whereRelation('organisation', 'id', $organisation->id);
    }

    /**
     * @param Builder      $builder
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return Builder
     */
    public function scopeAllForOrganisationUserAccess(Builder $builder, Organisation $organisation, User $user): Builder
    {
        return $builder->where(function ($q) use ($organisation, $user) {
            $q->forOrganisation($organisation);
            $q->orWhereRelation('libryo', function ($q) use ($organisation, $user) {
                $q->active()->userHasAccess($user);
                $q->whereRelation('organisation', 'id', $organisation->id);
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */
}
