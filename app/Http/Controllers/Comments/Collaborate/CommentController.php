<?php

namespace App\Http\Controllers\Comments\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\Collaborate\CommentRequest;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function HotwiredLaravel\TurboLaravel\dom_id;

class CommentController extends Controller
{
    /**
     * Get the comments for the given task or reference.
     *
     * @param Task           $task
     * @param Reference|null $reference
     *
     * @return PendingTurboStreamResponse
     */
    protected function index(Task $task, ?Reference $reference = null): PendingTurboStreamResponse
    {
        $comments = Comment::with(['task', 'author'])
            ->when(!$reference, function ($builder) use ($task) {
                $builder->where('task_id', $task->id);
            })
            ->when($reference, function ($builder) use ($reference) {
                /** @var Reference $reference */
                $builder->where('reference_id', $reference->id);
            })
            ->orderBy('id', 'desc')
            ->get();

        $params = [
            'task' => $task->id,
            'reference' => $reference->id ?? null,
        ];

        return singleTurboStreamResponse(dom_id($reference ?? $task, 'comments'))
            ->view('partials.comments.collaborate.comment.listing', [
                'comments' => $comments,
                'routeParams' => $params,
            ]);
    }

    /**
     * Store the comment.
     *
     * @param CommentRequest $request
     * @param Task           $task
     * @param Reference|null $reference
     *
     * @return RedirectResponse
     */
    public function store(CommentRequest $request, Task $task, ?Reference $reference = null): RedirectResponse
    {
        Comment::create([
            'reference_id' => $reference->id ?? null,
            'task_id' => $task->id,
            'task_type_id' => $task->task_type_id,
            'comment' => $request->get('comment'),
            'author_id' => Auth::id(),
        ]);

        return redirect()->route('collaborate.comments.index', [
            'task' => $task->id,
            'reference' => $reference->id ?? null,
        ]);
    }

    /**
     * Edit the given comment.
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return PendingTurboStreamResponse
     */
    public function edit(Request $request, Comment $comment): PendingTurboStreamResponse
    {
        $params = [
            'comment' => $comment->id,
        ];

        return singleTurboStreamResponse($request->header('turbo-frame') ?? '', 'update')
            ->view('partials.comments.collaborate.comment.listing', [
                'resource' => $comment,
                'comments' => [],
                'routeParams' => $params,
            ]);
    }

    /**
     * Edit the given comment.
     *
     * @param CommentRequest $request
     * @param Comment        $comment
     *
     * @return RedirectResponse
     */
    public function update(CommentRequest $request, Comment $comment): RedirectResponse
    {
        $comment->update(['comment' => $request->get('comment')]);

        return redirect()->route('collaborate.comments.index', [
            'task' => $comment->task_id,
            'reference' => $comment->reference_id,
        ]);
    }

    /**
     * Delete the comment.
     *
     * @param Comment $comment
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->route('collaborate.comments.index', [
            'task' => $comment->task_id,
            'reference' => $comment->reference_id,
        ]);
    }
}
