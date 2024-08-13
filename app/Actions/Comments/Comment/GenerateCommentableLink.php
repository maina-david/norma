<?php

namespace App\Actions\Comments\Comment;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Comments\Comment;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;

class GenerateCommentableLink
{
    public function handle(Comment $comment): string
    {
        if ($comment->commentable instanceof LegalUpdate) {
            $redirectTo = route('my.notify.legal-updates.show', ['update' => $comment->commentable_id], false);
        } elseif ($comment->commentable instanceof Reference) {
            $redirectTo = route('my.corpus.references.show', ['reference' => $comment->commentable_id], false);
        } elseif ($comment->commentable instanceof AssessmentItemResponse) {
            $redirectTo = route('my.assess.assessment-item.show', ['assessmentItem' => $comment->commentable->assessment_item_id], false);
        } elseif ($comment->commentable instanceof File) {
            $redirectTo = route('my.drives.files.show', ['file' => $comment->commentable_id], false);
        } elseif ($comment->commentable instanceof Libryo) {
            $redirectTo = route('my.dashboard', [], false);
        } elseif ($comment->commentable instanceof Task) {
            $redirectTo = route('my.tasks.tasks.show', ['task' => $comment->commentable->hash_id], false);
        } else {
            return '';
        }

        $libryoId = $comment->place_id;
        $activateRouteName = $libryoId ? 'my.libryos.activate.redirect' : 'my.libryos.activate.all.redirect';
        $activateRouteParams = $libryoId ? ['libryo' => $libryoId] : ['organisation' => $comment->organisation_id];

        return route($activateRouteName, array_merge($activateRouteParams, ['redirect' => $redirectTo]));
    }
}
