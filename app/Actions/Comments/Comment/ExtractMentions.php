<?php

namespace App\Actions\Comments\Comment;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ExtractMentions
{
    /**
     * @param Comment $comment
     *
     * @return Collection<User>
     */
    public function handle(Comment $comment): Collection
    {
        // regex returns the digits in the given string that match the user mentions. e.g. "hello **user_123**, how are you", will return 123
        $mentionedUserIds = Str::of($comment->comment)->matchAll('/\*\*user\_(\d+)\*\*/');

        /** @var Collection<User> */
        return count($mentionedUserIds) > 0 ? User::whereKey($mentionedUserIds)->get() : (new User())->newCollection();
    }
}
