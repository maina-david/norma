<?php

namespace App\Traits\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Comments\CommentReplacements;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Comment as OfficeComment;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait ExportsComments
{
    /** @var CommentReplacements|null */
    protected ?CommentReplacements $commentReplacements = null;

    /**
     * Prepare the comments by replacing the special characters.
     *
     * @param Collection<int, Comment> $comments
     *
     * @throws \Exception
     *
     * @return Collection<int, Comment>
     */
    protected function prepareComments(Collection $comments): Collection
    {
        if (!$this->commentReplacements) {
            $this->commentReplacements = app(CommentReplacements::class);
        }

        $comments = $this->commentReplacements->replaceContent($comments);

        /** @var Collection $comments */
        $comments->map(function ($comment) {
            /** @var Comment $comment */
            $comment->comment = strip_tags(str_replace('<br />', "\r\n", $comment->comment));

            return $comment;
        });

        return $comments;
    }

    /**
     * Eager load comments for the given Libryo.
     *
     * @param Libryo $libryo
     *
     * @return array<string|int, mixed>
     */
    protected function eagerCommentsForLibryo(Libryo $libryo): array
    {
        $libryo->load(['organisation']);

        return [
            'comments' => fn ($builder) => $builder
                ->where(fn ($q) => $q->forLibryo($libryo)->orWhere(fn ($query) => $query->forOrganisation($libryo->organisation)))
                ->reorder('id'),
            'comments.author',
            'comments.replies' => fn ($builder) => $builder->reorder('id', 'asc'),
            'comments.replies.author',
        ];
    }

    /**
     * Eager load comments for the given organisation.
     *
     * @param Organisation $organisation
     * @param User         $user
     *
     * @return array<string|int, mixed>
     */
    protected function eagerCommentsForOrganisation(Organisation $organisation, User $user): array
    {
        return [
            'comments' => fn ($builder) => $builder->allForOrganisationUserAccess($organisation, $user)->reorder('id'),
            'comments.author',
            'comments.replies' => fn ($builder) => $builder->reorder('id'),
            'comments.replies.author',
        ];
    }

    /**
     * Write the given comments to the worksheet at the given column.
     *
     * @param Collection<int, Comment> $comments
     * @param Worksheet                $sheet
     * @param string                   $column
     * @param string                   $labelColumn
     *
     * @throws Exception
     *
     * @return void
     */
    protected function writeComments(Collection $comments, Worksheet $sheet, string $column, string $labelColumn): void
    {
        $commentsAdded = __('interface.no');

        if (!$comments->isEmpty()) {
            $commentsAdded = __('interface.yes');
            $cellComment = $sheet->getComment($column);

            /** @var string $label */
            $label = __('customer.organisation.modules.comments');
            /** @var string $on */
            $on = __('comments.on');
            /** @var string $replyOn */
            $replyOn = __('comments.users_reply_on');

            $this->prepareComments($comments)->each(function ($comment) use ($cellComment, $on, $replyOn, $label) {
                /** @var Comment $comment */
                $this->writeSingleComment($comment, $cellComment, $on, $label);

                $comment->replies->each(function ($reply) use ($cellComment, $replyOn, $label) {
                    /** @var Comment $reply */
                    $this->writeSingleComment($reply, $cellComment, $replyOn, $label);
                });
            });
        }

        $sheet->setCellValue($labelColumn, $commentsAdded);
    }

    /**
     * Write the provided comment.
     *
     * @param Comment       $comment
     * @param OfficeComment $cellComment
     * @param string        $on
     * @param string        $label
     *
     * @return void
     */
    protected function writeSingleComment(Comment $comment, OfficeComment $cellComment, string $on, string $label): void
    {
        $date = $comment->created_at?->format('d M Y');

        $cellComment->setAuthor($label);

        $author = $comment->author->full_name ?? false;

        if ($author) {
            $cellComment->getText()
                ->createTextRun("{$author}{$on} {$date}:")
                ->getFont()
                ?->setBold(true);
            $cellComment->getText()->createTextRun("\r\n");
        }

        $cellComment->getText()->createTextRun($comment->comment);
        $cellComment->getText()->createTextRun("\r\n\r\n");
    }
}
