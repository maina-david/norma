<?php

namespace App\Events\Auth\UserActivity;

use App\Models\Auth\User;
use App\Models\Comments\Comment;

abstract class AbstractAddedComment extends UserActivityEvent implements AddedCommentEventInterface
{
    /** @var Comment */
    protected $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        /** @var User */
        $author = $comment->author;
        parent::__construct($author, $comment->libryo, $comment->organisation);
        $this->comment = $comment;
    }

    /**
     * @return string|false
     **/
    public function toJson(int $options = 0): string|false
    {
        $arr = [
            'comment_id' => $this->comment->id,
        ];
        if ($this->comment->commentable instanceof Comment) {
            $arr['reply_to_id'] = $this->comment->commentable_id;
        }

        return json_encode($arr, $options);
    }
}
