<?php

namespace App\Notifications\Comments;

use App\Actions\Comments\Comment\GenerateCommentableLink;
use App\Enums\Notifications\NotificationType;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Notifications\AbstractNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class MentionedNotification extends AbstractNotification
{
    use Queueable;

    public function __construct(protected int $commentId)
    {
        parent::__construct(NotificationType::mention()->value, 'comments.comment.mentioned_in_comment', []);
    }

    /**
     * {@inheritDoc}
     */
    public function via($notifiable): array
    {
        return $notifiable->shouldReceiveMentionEmail() ? ['mail', 'database'] : ['database'];
    }

    /**
     * @param User $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        /** @var Comment */
        $comment = Comment::with('commentable')->findOrFail($this->commentId);
        /** @var string */
        $subject = __('comments.comment.mentioned_in_comment');
        /** @var string */
        $action = __('comments.comment.view_comment');
        $link = app(GenerateCommentableLink::class)->handle($comment);

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
