<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\Comments\Collaborate\CommentRequest;
use App\Http\Resources\Internals\Collaborate\Comments\CommentResource;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceCommentsController extends AbstractApiController
{
    /**
     * {@inheritDoc}
     */
    public function getQuery(Request $request): Builder
    {
        $reference = $request->route('reference');

        return parent::getQuery($request)
            ->where('reference_id', $reference)
            ->with(['taskType']);
    }

    /**
     * {@inheritDoc}
     */
    protected function getModelClass(): string
    {
        return Comment::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiResourceClass(): string
    {
        return CommentResource::class;
    }

    /**
     * Store a new comment.
     *
     * @param CommentRequest $request
     * @param Reference      $reference
     * @param Task           $task
     *
     * @return CommentResource
     */
    public function store(CommentRequest $request, Reference $reference, Task $task): CommentResource
    {
        $comment = Comment::create([
            'reference_id' => $reference->id,
            'task_id' => $task->id,
            'task_type_id' => $task->task_type_id,
            'comment' => $request->get('comment'),
            'author_id' => Auth::id(),
        ]);

        return new CommentResource($comment);
    }

    /**
     * Update an existing comment.
     *
     * @param CommentRequest                           $request
     * @param Reference                                $reference
     * @param Task                                     $task
     * @param \App\Models\Comments\Collaborate\Comment $comment
     *
     * @return CommentResource
     */
    public function update(CommentRequest $request, Reference $reference, Task $task, Comment $comment): CommentResource
    {
        $this->authorize('update', $comment);
        $comment->update(['comment' => $request->get('comment')]);

        return new CommentResource($comment);
    }

    /**
     * Delete an existing comment.
     *
     * @param Reference                                $reference
     * @param Task                                     $task
     * @param \App\Models\Comments\Collaborate\Comment $comment
     *
     * @return CommentResource
     */
    public function destroy(Reference $reference, Task $task, Comment $comment): CommentResource
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return new CommentResource($comment);
    }
}
