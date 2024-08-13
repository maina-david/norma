<?php

namespace App\Actions\Comments\Comment;

use App\Models\Comments\Comment;
use App\Services\HTMLPurifierService;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCommentSaving
{
    use AsAction;

    public function __construct(protected HTMLPurifierService $htmlPurifierService)
    {
    }

    public function handle(Comment $comment): void
    {
        $comment->comment = $this->htmlPurifierService->cleanComment($comment->comment);
    }
}
