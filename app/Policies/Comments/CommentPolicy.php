<?php

namespace App\Policies\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the comment.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function delete(User $user, Comment $comment)
    {
        return (bool) ($user->id === $comment->author_id);
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function attachFiles(User $user, Comment $comment)
    {
        return (bool) ($user->id === $comment->author_id);
    }
}
