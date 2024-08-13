<?php

namespace App\Notifications\Comments;

use App\Actions\Comments\Comment\GenerateCommentableLink;
use App\Enums\Notifications\NotificationType;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Notifications\AbstractNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class CommentReplyNotification extends AbstractNotification
{
    use Queueable;

    public function __construct(protected int $commentId)
    {
        parent::__construct(NotificationType::mention()->value, 'comments.comment.someone_replied', []);
    }

    /**
     * {@inheritDoc}
     */
    public function via($notifiable): array
    {
        return $notifiable->shouldReceiveReplyEmail() ? ['mail', 'database'] : ['database'];
    }

    /**
     * @param User $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        /** @var Comment */
        $comment = Comment::with('commentable.commentable')->findOrFail($this->commentId);
        /** @var string */
        $subject = __('comments.comment.someone_replied');
        /** @var string */
        $action = __('comments.comment.view_comment');
        /** @var Comment $commentable */
        $commentable = $comment->commentable;
        $link = app(GenerateCommentableLink::class)->handle($commentable);

        return (new MailMessage())
            ->subject($subject)
            ->line($subject)
            ->action($action, $link);
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    public function databaseFormat($notifiable): array
    {
        return [
            'comment_id' => $this->commentId,
        ];
    }
}
