<?php

namespace App\Models\Traits;

use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        /** @var MorphMany */
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'DESC');
    }
}
