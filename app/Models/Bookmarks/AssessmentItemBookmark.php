<?php

namespace App\Models\Bookmarks;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin IdeHelperAssessmentItemBookmark
 */
class AssessmentItemBookmark extends AbstractModel
{
    /**
     * Filter for the given user.
     *
     * @param Builder $builder
     * @param User    $user
     *
     * @return Builder
     */
    public function scopeForUser(Builder $builder, User $user): Builder
    {
        return $builder->where('user_id', $user->id);
    }
}
