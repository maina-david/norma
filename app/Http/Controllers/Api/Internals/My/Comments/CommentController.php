<?php

namespace App\Http\Controllers\Api\Internals\My\Comments;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Requests\Comments\CommentRequest;
use App\Http\Resources\Internals\My\Comments\CommentResource;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Tasks\Task;
use App\Services\Comments\CommentReplacements;
use App\Services\Customer\ActiveNormasManager;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use IteratorAggregate;

class CommentController
{
    /** @var array<string, class-string> */
    protected array $allowed = [
        'task' => Task::class,
        'comment' => Comment::class,
    ];

    /**
     * @codeCoverageIgnore
     *
     * @param Task $model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    protected function baseQuery(Model $model): MorphMany
    {
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();

        if ($norma = $manager->getActive()) {
            return $model->comments()
                ->where(function ($builder) use ($norma, $organisation) {
                    $builder->forNorma($norma)->orWhere(function ($query) use ($organisation) {
                        $query->forOrganisation($organisation);
                    });
                });
        }

        /** @var User $user */
        $user = Auth::user();

        return $model->comments()->allForOrganisationUserAccess($organisation, $user);
    }

    /**
     * Get the listing of the given relation.
     *
     * @param \App\Services\Comments\CommentReplacements $commentReplacements
     * @param string                                     $relation
     * @param int                                        $related
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(CommentReplacements $commentReplacements, string $relation, int $related): AnonymousResourceCollection
    {
        abort_unless(in_array($relation, array_keys($this->allowed)), 404);

        /** @var Task $model */
        $model = $this->allowed[$relation]::findOrFail($related);

        /** @var IteratorAggregate<\App\Models\Comments\Comment> $comments */
        $comments = $this->baseQuery($model)
            ->with(['author'])
            ->withCount(['comments', 'files'])
            ->paginate(200);

        $comments = $commentReplacements->replaceContent($comments);

        return CommentResource::collection($comments);
    }

    /**
     * Create a comment.
     *
     * @param \App\Services\Comments\CommentReplacements $commentReplacements
     * @param \App\Http\Requests\Comments\CommentRequest $request
     * @param string                                     $relation
     * @param int                                        $related
     *
     * @return \App\Http\Resources\Internals\My\Comments\CommentResource
     */
    public function store(CommentReplacements $commentReplacements, CommentRequest $request, string $relation, int $related): CommentResource
    {
        abort_unless(in_array($relation, array_keys($this->allowed)), 404);

        /** @var Task $model */
        $model = $this->allowed[$relation]::findOrFail($related);

        /** @var User $user */
        $user = Auth::user();

        $targetNorma = $request->get('target_norma_id') ? Norma::whereKey($request->get('target_norma_id'))->with('organisation')->userHasAccess($user)->firstOrFail() : null;

        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma $norma */
            $norma = $manager->getActive();
        }

        /** @var Comment $created */
        $created = Comment::create([
            'commentable_type' => $model->getMorphClass(),
            'commentable_id' => $model->id,
            'place_id' => $targetNorma->id ?? $norma->id ?? null,
            'organisation_id' => $manager->getActiveOrganisation()->id ?? null,
            'comment' => $request->get('comment'),
            'author_id' => $user->id,
        ]);

        $created->setRelation('author', $user);
        $created->setAttribute('comments_count', 0);
        $created->setAttribute('files_count', 0);

        $this->logActivity($model->getMorphClass(), $created->id);

        $comments = collect([$created]);

        $comments = $commentReplacements->replaceContent($comments);

        /** @var \Illuminate\Database\Eloquent\Collection $comments */

        return new CommentResource($comments[0]);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $type
     * @param int    $id
     *
     * @return void
     */
    protected function logActivity(string $type, int $id): void
    {
        if ($type === (new ContextQuestion())->getMorphClass()) {
            event(new GenericActivityUsingAuth(UserActivityType::commentedOnApplicability(), ['id' => $id]));
        }
    }

    /**
     * Delete the comment.
     *
     * @param \App\Models\Comments\Comment $comment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([]);
    }
}
