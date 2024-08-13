<?php

namespace App\Observers\Comments;

use App\Actions\Comments\Comment\HandleCommentCreated;
use App\Actions\Comments\Comment\HandleCommentSaving;
use App\Models\Comments\Comment;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     *
     * @param \App\Models\Comments\Comment $comment
     *
     * @return void
     */
    public function created(Comment $comment)
    {
        HandleCommentCreated::run($comment);
    }

    /**
     * Listen to the Comment saving event.
     *
     * @param Comment $comment
     *
     * @return void
     */
    public function saving(Comment $comment): void
    {
        HandleCommentSaving::run($comment);
    }
}
