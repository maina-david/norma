<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Notify\LegalUpdate;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;

class AddedCommentReply extends AbstractAddedComment
{
    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        // @phpstan-ignore-next-line
        $commentable = $this->comment->commentable->commentable;

        if ($commentable instanceof Reference) {
            return UserActivityType::referenceCommentReply();
        }
        if ($commentable instanceof LegalUpdate) {
            return UserActivityType::legalUpdateCommentReply();
        }
        if ($commentable instanceof File) {
            return UserActivityType::documentCommentReply();
        }
        if ($commentable instanceof AssessmentItemResponse) {
            return UserActivityType::assessmentItemResponseCommentReply();
        }
        if ($commentable instanceof Task) {
            return UserActivityType::taskCommentReply();
        }

        // @codeCoverageIgnoreStart
        return UserActivityType::libryoCommentReply();
        // @codeCoverageIgnoreEnd
    }
}
