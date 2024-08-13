<?php

namespace Tests\Unit\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\AddedCommentReply;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Comments\Comment;
use App\Models\Corpus\Reference;
use App\Models\Notify\LegalUpdate;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class AddedCommentReplyTest extends MyTestCase
{
    public function testAddedCommentReply(): void
    {
        $reference = Reference::factory()->create();
        $file = File::factory()->create();
        $task = Task::factory()->create();
        $update = LegalUpdate::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->create();

        $comment = Comment::factory()->create(['commentable_type' => 'file', 'commentable_id' => $file->id]);
        $replyComment = Comment::factory()->create(['commentable_type' => 'comment', 'commentable_id' => $comment->id]);
        $event = new AddedCommentReply($replyComment);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::documentCommentReply()));

        $comment = Comment::factory()->create(['commentable_type' => 'task', 'commentable_id' => $task->id]);
        $replyComment = Comment::factory()->create(['commentable_type' => 'comment', 'commentable_id' => $comment->id]);
        $event = new AddedCommentReply($replyComment);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::taskCommentReply()));

        $comment = Comment::factory()->create(['commentable_type' => 'register_notification', 'commentable_id' => $update->id]);
        $replyComment = Comment::factory()->create(['commentable_type' => 'comment', 'commentable_id' => $comment->id]);
        $event = new AddedCommentReply($replyComment);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::legalUpdateCommentReply()));

        $comment = Comment::factory()->create(['commentable_type' => 'assessment_item_response', 'commentable_id' => $aiResponse->id]);
        $replyComment = Comment::factory()->create(['commentable_type' => 'comment', 'commentable_id' => $comment->id]);
        $event = new AddedCommentReply($replyComment);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::assessmentItemResponseCommentReply()));

        $comment = Comment::factory()->create(['commentable_type' => 'register_item', 'commentable_id' => $reference->id]);
        $replyComment = Comment::factory()->create(['commentable_type' => 'comment', 'commentable_id' => $comment->id]);
        $event = new AddedCommentReply($replyComment);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::referenceCommentReply()));
    }
}
