<?php

namespace App\Http\Controllers\Storage\My;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FileStreamController
{
    /**
     * @param AssessmentItemResponse $response
     *
     * @return Response
     */
    public function forAssessmentItemResponse(AssessmentItemResponse $response): Response
    {
        /** @var View */
        $view = view('streams.single-partial', $this->getViewParamsForRelation(
            $response,
            'assessment-item-response',
            $response->files()->with(['folder'])->paginate(),
        ));

        return turboStreamResponse($view);
    }

    /**
     * @param Task $task
     *
     * @return Response
     */
    public function forTask(Task $task): Response
    {
        /** @var View */
        $view = view('streams.single-partial', $this->getViewParamsForRelation(
            $task,
            'task',
            $task->files()->with(['folder'])->paginate(),
        ));

        return turboStreamResponse($view);
    }

    /**
     * @param Comment $comment
     *
     * @return Response
     */
    public function forComment(Comment $comment): Response
    {
        /** @var View */
        $view = view('streams.single-partial', $this->getViewParamsForRelation(
            $comment,
            'comment',
            $comment->files()->with(['folder'])->paginate(),
        ));

        return turboStreamResponse($view);
    }

    /**
     * @param Model                $model
     * @param string               $relation
     * @param LengthAwarePaginator $files
     *
     * @return array<string, mixed>
     */
    public function getViewParamsForRelation(Model $model, string $relation, LengthAwarePaginator $files): array
    {
        /** @var User $user */
        $user = Auth::user();

        if ($model->isRelation('norma')) {
            $model->load(['norma']);
        }

        return [
            'partialView' => 'partials.storage.my.file.files-for',
            'target' => 'files-for-' . $relation . '-' . $model->getKey(),
            'files' => $files,
            'related' => $model,
            'relation' => $relation,
            'upload' => $relation !== 'comment' || $user->can('attachFiles', $model),
            'norma' => $model->norma ?? null,
        ];
    }
}
