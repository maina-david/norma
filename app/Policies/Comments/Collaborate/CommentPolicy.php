<?php

namespace App\Policies\Comments\Collaborate;

use App\Models\Auth\User;
use App\Models\Comments\Collaborate\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    //    public function viewAny(User $user)
    //    {
    //        return $user->can('collaborate.comments.collaborate.comment.viewAny');
    //    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return Response|bool
     */
    //    public function view(User $user, Comment $comment)
    //    {
    //        return $comment->isAuthor($user) || $user->canAny([
    //            'collaborate.comments.collaborate.comment.view',
    //            'collaborate.comments.collaborate.comment.create',
    //            'collaborate.comments.collaborate.comment.update',
    //            'collaborate.comments.collaborate.comment.delete',
    //        ]);
    //    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    //    public function create(User $user)
    //    {
    //        return $user->can('collaborate.comments.collaborate.comment.create');
    //    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return Response|bool
     */
    public function update(User $user, Comment $comment)
    {
        return $comment->isAuthor($user) || $user->can('collaborate.comments.collaborate.comment.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return Response|bool
     */
    public function delete(User $user, Comment $comment)
    {
        return $comment->isAuthor($user) || $user->can('collaborate.comments.collaborate.comment.delete');
    }
}
