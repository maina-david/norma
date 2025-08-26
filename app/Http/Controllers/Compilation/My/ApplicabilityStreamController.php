<?php

namespace App\Http\Controllers\Compilation\My;

use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Services\Comments\CommentReplacements;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use IteratorAggregate;

class ApplicabilityStreamController
{
    /**
     * Get the requirements that match the given context question and norma.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param \App\Models\Customer\Norma             $norma
     *
     * @return Response
     */
    public function requirements(ContextQuestion $question, Norma $norma): Response
    {
        $norma->load(['requirementsCollections', 'legalDomains']);

        $query = $question->references()
            ->forNormaAutocompiledBase($norma)
            ->orderBy('work_id')
            ->orderedPosition()
            ->with(['citation', 'refPlainText', 'legalDomains', 'locations', 'work']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.for-related',
            'target' => 'requirements-for-context-question-' . $question->id,
            'baseQuery' => $query,
            'empty' => $query->count() === 0,
            'route' => route('my.references.for.context-questions.index', ['norma' => $norma->id, 'question' => $question->id]),
            'editable' => false,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the possible assessment items for the given norma and context question.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param \App\Models\Customer\Norma             $norma
     *
     * @return Response
     */
    public function assessmentItems(ContextQuestion $question, Norma $norma): Response
    {
        $query = AssessmentItem::possibleForUncompiledNorma($norma, $question)->with(['legalDomain.topParent']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.assessment-item.for-related',
            'target' => 'assessment-for-context-question-' . $question->id,
            'baseQuery' => $query,
            'empty' => $query->count() === 0,
            'route' => route('my.assessment-items.for.context-questions.index', ['norma' => $norma->id, 'question' => $question->id]),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the possible assessment items for the given norma and context question.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param \App\Models\Customer\Norma             $norma
     *
     * @return Response
     */
    public function actionAreas(ContextQuestion $question, Norma $norma): Response
    {
        $actions = ActionArea::possibleForNormaInApplicability($norma, $question)->get();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.actions.my.action-area.for-related',
            'target' => 'action-areas-for-context-question-' . $question->id,
            'actions' => $actions,
            'route' => route('my.action-areas.for.context-questions.index', ['norma' => $norma->id, 'question' => $question->id]),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the activities for the given norma and context question.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param \App\Models\Customer\Norma             $norma
     *
     * @return Response
     */
    public function activities(ContextQuestion $question, Norma $norma): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.context-question.for-response-index',
            'target' => 'activities-for-context-question-' . $question->id,
            'activities' => $question->activities()
                ->where('place_id', $norma->id)
                ->with(['user'])
                ->orderByDesc('id')
                ->simplePaginate(25),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the task listing for the context question and norma.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param \App\Models\Customer\Norma             $norma
     *
     * @return \Illuminate\Http\Response
     */
    public function tasks(ContextQuestion $question, Norma $norma): Response
    {
        $query = $question->tasks()->forNorma($norma)->with(['author', 'assignee']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task.tasks-for',
            'target' => 'tasks-for-context-question-' . $question->id,
            'query' => $query,
            'relation' => 'context-question',
            'related' => $question,
            'taskableType' => $question->getMorphClass(),
            'taskableId' => $question->id,
            'norma' => $norma,
        ]);

        return turboStreamResponse($view);
    }

    public function comments(ContextQuestion $question, Norma $norma): Response
    {
        $norma->load(['organisation']);
        /** @var \App\Models\Customer\Organisation $organisation */
        $organisation = $norma->organisation;
        $query = $question->tasks()->forNorma($norma)->with(['author', 'assignee']);

        /** @var IteratorAggregate<\App\Models\Comments\Comment> $comments */
        $comments = $question->comments()
            ->where(function ($builder) use ($organisation, $norma) {
                $builder->forNorma($norma)->orWhere(fn ($query) => $query->forOrganisation($organisation));
            })
            ->with(['author'])
            ->withCount(['comments', 'files'])
            ->simplePaginate(100);

        $comments = app(CommentReplacements::class)->replaceContent($comments);

        /** @var User $user */
        $user = Auth::user();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.comments.comment.my.for-commentable',
            'target' => 'comments-for-context-question-' . $question->id,
            'comments' => $comments,
            'commentableType' => $question->getMorphClass(),
            'commentableId' => $question->id,
            'user' => $user,
            'norma' => $norma,
            'redirect' => route('my.comments.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]),
        ]);

        return turboStreamResponse($view);
    }
}
