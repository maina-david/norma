<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Corpus\UpdateCandidateActionStatus;
use App\Http\Controllers\Controller;
use App\Models\Corpus\Doc;
use App\Models\Notify\NotificationAction;
use App\Models\Notify\UpdateCandidate;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCandidateAdditionalActionsController extends Controller
{
    /**
     * Return the view.
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function index(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load('updateCandidate.notificationActions');

        return singleTurboStreamResponse("doc-additional-actions-{$doc->id}")
            ->view('pages.corpus.collaborate.doc.partials.additional-actions-column', ['row' => $doc]);
    }

    /**
     * Get the form.
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function create(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load('updateCandidate.notificationActions');

        $existing = $doc->updateCandidate->notificationActions ?? collect();

        $actions = NotificationAction::whereNotIn('id', $existing->pluck('id')->toArray())
            ->pluck('text', 'id')
            ->toArray();

        return singleTurboStreamResponse("doc-additional-actions-{$doc->id}-form")
            ->view('partials.notify.update-candidate.additional-actions', ['resource' => $doc, 'actions' => $actions]);
    }

    /**
     * Update the actions status.
     *
     * @param Request $request
     * @param Doc     $doc
     *
     * @return RedirectResponse
     */
    public function store(Request $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate $candidate */
        $candidate = UpdateCandidate::where('doc_id', $doc->id)->firstOrFail();

        $request->collect()
            ->each(function ($val, $key) use ($candidate) {
                if (str_starts_with($key, 'action')) {
                    $candidate->notificationActions()
                        ->updateExistingPivot(str_replace('action', '', $key), ['status' => $val]);
                }
            });

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.additional-actions.index', ['doc' => $doc->id]);
    }

    /**
     * Update the selected actions.
     *
     * @param Request $request
     * @param Doc     $doc
     *
     * @return RedirectResponse
     */
    public function sync(Request $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate $candidate */
        $candidate = UpdateCandidate::where('doc_id', $doc->id)->firstOrFail();

        $candidate->notificationActions()->attach($request->get('additional_actions'), [
            'status' => UpdateCandidateActionStatus::PENDING->value,
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.additional-actions.create', ['doc' => $doc->id]);
    }

    /**
     * Delete the action.
     *
     * @param Doc                $doc
     * @param NotificationAction $action
     *
     * @return RedirectResponse
     */
    public function destroy(Doc $doc, NotificationAction $action): RedirectResponse
    {
        $doc->load(['updateCandidate']);

        if ($doc->updateCandidate) {
            $doc->updateCandidate->notificationActions()->detach($action->id);
        }

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.additional-actions.create', ['doc' => $doc->id]);
    }
}
