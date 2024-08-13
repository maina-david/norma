<?php

namespace App\Traits\Bookmarks;

use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;

trait UsesBookmarksModelFilter
{
    /**
     * @param string $value
     *
     * @return self
     */
    public function bookmarked(string $value): self
    {
        $value = (int) $value;

        /** @var User|null $user */
        $user = Auth::user() ?? User::find($value);

        // @codeCoverageIgnoreStart
        if (!$user) {
            return $this;
        }
        // @codeCoverageIgnoreEnd

        /** @var self */
        return $this->whereHas('bookmarks', fn ($query) => $query->forUser($user));
    }
}
