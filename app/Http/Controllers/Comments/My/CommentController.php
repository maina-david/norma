<?php

namespace App\Http\Controllers\Comments\My;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsModelForMorph;
use App\Http\Requests\Comments\CommentRequest;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Comments\CommentReplacements;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    use GetsModelForMorph;

    public function __construct(
        protected CommentReplacements $commentReplacements
    ) {
    }

    /**
     * @return array<mixed>
     */
    private function getNormaOrgQuery(Model $model): array
    {
        $norma = null;
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $query = $model->comments()->where(function ($builder) use ($norma, $organisation) {
                $builder->forNorma($norma)
                    ->orWhere(function ($query) use ($organisation) {
                        $query->forOrganisation($organisation);
                    });
            });
        } else {
            /** @var User */
            $user = Auth::user();
            /** @var Organisation */
            $query = $model->comments()->allForOrganisationUserAccess($organisation, $user);
        }

        return [$norma, $organisation, $query];
    }

    /**
     * @param string $type
     * @param int    $id
     *
     * @return Response
     */
    public function forCommentable(string $type, int $id): Response
    {
        $model = $this->getModel($type, $id, 'commentable');
        [$norma, $organisation, $query] = $this->getNormaOrgQuery($model);

        $comments = $query->with(['author'])->withCount(['comments', 'files'])->simplePaginate(100);
        $comments = $this->commentReplacements->replaceContent($comments);

        /** @var User $user */
        $user = Auth::user();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.comments.comment.my.for-commentable',
            'target' => 'comments-for-' . $type . '-' . $id,
            'comments' => $comments,
            'commentableType' => $type,
            'commentableId' => $id,
            'user' => $user,
        ]);

        return turboStreamResponse($view);
    }

    public function storeForCommentable(CommentRequest $request, string $type, int $id): Response|RedirectResponse
    {
        $data = $request->validated();
        $legacyType = $this->getLegacyName($type, 'commentable');

        /** @var User */
        $user = Auth::user();

        $targetNorma = $request->get('target_norma_id') ? Norma::whereKey($request->get('target_norma_id'))->with('organisation')->userHasAccess($user)->firstOrFail() : null;

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
        }

        /** @var Comment $created */
        $created = Comment::create([
            'commentable_type' => $legacyType,
            'commentable_id' => $id,
            'place_id' => $targetNorma->id ?? $norma->id ?? null,
            'organisation_id' => $organisation->id ?? null,
            'comment' => $data['comment'],
            'author_id' => $user->id,
        ]);

        $this->logActivity($type, $created->id);

        if ($request->has('save_and_back')) {
            /** @var RedirectResponse */
            return redirect($request->get('save_and_back'));
        }

        return $this->forCommentable($type, $id);
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

    public function destroy(Request $request, Comment $comment): Response|RedirectResponse
    {
        $comment->delete();
        $type = $this->getSlugFromLegacyName($comment->commentable_type, 'commentable');

        if ($request->has('save_and_back')) {
            /** @var RedirectResponse */
            return redirect($request->get('save_and_back'));
        }

        return $this->forCommentable($type, $comment->commentable_id);
    }
}
