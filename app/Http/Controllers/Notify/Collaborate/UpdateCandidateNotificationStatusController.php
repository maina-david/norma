<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Notify\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notify\UpdateCandidateNotificationStatusRequest;
use App\Models\Corpus\Doc;
use App\Models\Notify\NotificationStatusReason;
use App\Models\Notify\UpdateCandidate;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;

class UpdateCandidateNotificationStatusController extends Controller
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
        $doc->load('updateCandidate.notificationStatusReason');

        return singleTurboStreamResponse("doc-notification-status-{$doc->id}")
            ->view('pages.corpus.collaborate.doc.partials.notification-status-column', ['row' => $doc]);
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
        $doc->load('updateCandidate');

        $statuses = NotificationStatus::forSelector();

        return singleTurboStreamResponse("doc-notification-status-{$doc->id}-form")
            ->view('partials.notify.update-candidate.notification-status', [
                'resource' => $doc,
                'statuses' => $statuses,
            ]);
    }

    /**
     * Update the affected legislation.
     *
     * @param UpdateCandidateNotificationStatusRequest $request
     * @param Doc                                      $doc
     *
     * @return RedirectResponse
     */
    public function store(UpdateCandidateNotificationStatusRequest $request, Doc $doc): RedirectResponse
    {
        /** @var UpdateCandidate $candidate */
        $candidate = UpdateCandidate::with(['notificationStatusReason'])->where('doc_id', $doc->id)->firstOrFail();

        /** @var NotificationStatusReason $reason */
        $reason = $candidate->notificationStatusReason ?? new NotificationStatusReason();
        $reason->text = $request->get('notification_status_reason');
        $reason->save();

        $candidate->update([
            'notification_status' => $request->get('notification_status'),
            'notification_status_reason_id' => $reason->id,
        ]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.notification-status.index', ['doc' => $doc->id]);
    }
}
