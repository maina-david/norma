<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Corpus\UpdateCandidateActionStatus;
use App\Http\Controllers\Controller;
use App\Models\Corpus\Doc;
use App\Models\Notify\NotificationHandover;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use App\Models\Notify\UpdateCandidateLegislation;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCandidateHandoverUpdateController extends Controller
{
    /**
     * Get the form.
     *
     * @param Doc $doc
     *
     * @return PendingTurboStreamResponse
     */
    public function create(Doc $doc): PendingTurboStreamResponse
    {
        $doc->load([
            'updateCandidate.legislation.handovers.board',
            'updateCandidate.legislation.work:id,uid',
            'updateCandidate.legislation.work.catalogueDoc:id,uid',
        ]);

        $actions = NotificationHandover::pluck('text', 'id');

        return singleTurboStreamResponse("doc-handover-update-{$doc->id}-form")
            ->view('partials.notify.update-candidate.handover-update', ['resource' => $doc, 'actions' => $actions]);
    }

    /**
     * Update the handover status.
     *
     * @param Request                                       $request
     * @param Doc                                           $doc
     * @param \App\Models\Notify\UpdateCandidateLegislation $legislation
     *
     * @return RedirectResponse
     */
    public function store(Request $request, Doc $doc, UpdateCandidateLegislation $legislation): RedirectResponse
    {
        $legislation->update(['handover_comments' => $request->get('handover_comments')]);

        $toUpdate = [];
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'action')) {
                /** @var array<string, mixed> $meta */
                $meta = $request->get("meta_{$key}", []);

                $toUpdate[] = [
                    'notification_handover_id' => (int) str_replace('action', '', $key),
                    'update_candidate_legislation_id' => $legislation->id,
                    'status' => $value,
                    'meta' => json_encode($meta),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        NotificationHandoverUpdateCandidateLegislation::upsert(
            $toUpdate,
            ['notification_handover_id', 'update_candidate_legislation_id'],
            ['status', 'meta', 'updated_at']
        );

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]);
    }

    /**
     * Update the selected actions.
     *
     * @param Request                                       $request
     * @param Doc                                           $doc
     * @param \App\Models\Notify\UpdateCandidateLegislation $legislation
     *
     * @return RedirectResponse
     */
    public function sync(Request $request, Doc $doc, UpdateCandidateLegislation $legislation): RedirectResponse
    {
        $handovers = $request->collect('handover_update');

        if ($handovers->isNotEmpty()) {
            $handovers = $handovers->map(function ($handoverId) use ($legislation) {
                return [
                    'notification_handover_id' => $handoverId,
                    'update_candidate_legislation_id' => $legislation->id,
                    'status' => UpdateCandidateActionStatus::PENDING->value,
                    'meta' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
                ->all();

            NotificationHandoverUpdateCandidateLegislation::insertOrIgnore($handovers);
        }

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]);
    }

    /**
     * Delete the action.
     *
     * @param Doc                                           $doc
     * @param \App\Models\Notify\UpdateCandidateLegislation $legislation
     * @param NotificationHandover                          $update
     *
     * @return RedirectResponse
     */
    public function destroy(Doc $doc, UpdateCandidateLegislation $legislation, NotificationHandover $update): RedirectResponse
    {
        NotificationHandoverUpdateCandidateLegislation::where('notification_handover_id', $update->id)
            ->where('update_candidate_legislation_id', $legislation->id)
            ->delete();

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]);
    }
}
