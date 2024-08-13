<?php

namespace App\Http\Controllers\Assess\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assess\AssessmentItemContextQuestionRequest;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Stores\Assess\AssessmentItemStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class AssessmentItemContextQuestionController extends Controller
{
    /**
     * @param AssessmentItemStore $store
     */
    public function __construct(protected AssessmentItemStore $store)
    {
    }

    /**
     * Handle the attachment of context questions.
     *
     * @param AssessmentItem                       $item
     * @param AssessmentItemContextQuestionRequest $request
     *
     * @return RedirectResponse
     */
    public function store(AssessmentItem $item, AssessmentItemContextQuestionRequest $request): RedirectResponse
    {
        $this->store->attachContextQuestions($item, $request->collect('context_questions'));

        Session::flash('flash.message', __('notifications.successfully_updated'));

        return $this->redirectTo($item);
    }

    /**
     * Handle the detachment of the context question.
     *
     * @param AssessmentItem  $item
     * @param ContextQuestion $question
     *
     * @return RedirectResponse
     */
    public function destroy(AssessmentItem $item, ContextQuestion $question): RedirectResponse
    {
        $this->store->detachContextQuestions($item, collect([$question->id]));

        Session::flash('flash.message', __('notifications.successfully_deleted'));

        return $this->redirectTo($item);
    }

    /**
     * Redirect back to the assessment item.
     *
     * @param AssessmentItem $item
     *
     * @return RedirectResponse
     */
    protected function redirectTo(AssessmentItem $item): RedirectResponse
    {
        return redirect()->route('collaborate.assessment-items.show', [
            'assessment_item' => $item->id,
            'tab' => 'context',
        ]);
    }
}
