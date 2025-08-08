<?php

namespace App\Actions\Comments\Comment;

use App\Enums\Tasks\TaskActivityType;
use App\Events\Assess\AssessmentActivity\LeftNote;
use App\Events\Auth\UserActivity\AddedAssessmentItemResponseComment;
use App\Events\Auth\UserActivity\AddedCommentReply;
use App\Events\Auth\UserActivity\AddedDocumentComment;
use App\Events\Auth\UserActivity\AddedLegalUpdateComment;
use App\Events\Auth\UserActivity\AddedNormaComment;
use App\Events\Auth\UserActivity\AddedReferenceComment;
use App\Events\Auth\UserActivity\AddedTaskComment;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Tasks\Task;
use App\Notifications\Comments\CommentReplyNotification;
use App\Notifications\Comments\MentionedNotification;
use App\Stores\Tasks\TaskActivityStore;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCommentCreated
{
    use AsAction;

    public function __construct(protected TaskActivityStore $taskActivityStore, protected ExtractMentions $extractMentions)
    {
    }

    /**
     * @param Comment $comment
     *
     * @return void
     */
    public function handle(Comment $comment): void
    {
        /** @var User|null */
        $user = Auth::user();

        if (is_null($user)) {
            return;
        }

        $this->extractMentionsAndSend($comment);

        $this->raiseEvent($comment, $user);
    }

    /**
     * @param Comment $comment
     * @param User    $user
     *
     * @return void
     */
    public function raiseEvent(Comment $comment, User $user): void
    {
        switch ($comment->commentable_type) {
            case 'comment':
                AddedCommentReply::dispatch($comment);
                $this->handleCommentReply($comment);
                break;
            case 'task':
                AddedTaskComment::dispatch($comment);
                $this->handleTaskComment($comment, $user);
                break;
            case 'place':
                AddedNormaComment::dispatch($comment);
                break;
            case 'register_item':
                AddedReferenceComment::dispatch($comment);
                break;
            case 'file':
                AddedDocumentComment::dispatch($comment);
                break;
            case 'register_notification':
                AddedLegalUpdateComment::dispatch($comment);
                break;
            case 'assessment_item_response':
                AddedAssessmentItemResponseComment::dispatch($comment);
                /** @var AssessmentItemResponse $aiResponse */
                $aiResponse = $comment->commentable;
                LeftNote::dispatch(
                    $user,
                    $aiResponse,
                    $comment
                );
                break;
                // @codeCoverageIgnoreStart
            default:
                break;
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param Comment $comment
     * @param User    $user
     *
     * @return void
     */
    protected function handleTaskComment(Comment $comment, User $user): void
    {
        /** @var Task $task */
        $task = $comment->commentable;
        $this->taskActivityStore->create(
            TaskActivityType::madeComment(),
            $comment->commentable_id,
            $user->id,
            $task->place_id,
            ['comment_id' => $comment->id]
        );
    }

    protected function extractMentionsAndSend(Comment $comment): void
    {
        $users = $this->extractMentions->handle($comment);
        foreach ($users as $user) {
            $user->notify(new MentionedNotification($comment->id));
        }
    }

    protected function handleCommentReply(Comment $comment): void
    {
        /** @var Comment */
        $commentedOn = $comment->commentable;
        // don't notify if replying to your own comment
        if ($comment->author_id === $commentedOn->author_id) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $commentedOn->author?->notify(new CommentReplyNotification($comment->id));
    }
}
