<?php

namespace App\Actions\Notify\LegalUpdate;

use App\Models\Notify\LegalUpdate;
use App\Models\Notify\UpdateCandidateLegislation;

class SyncMaintainedWorks
{
    /**
     * Sync the works that have been maintained by the legal update created from the given doc.
     *
     * @param int $docId
     *
     * @return void
     */
    public function handle(int $docId): void
    {
        $update = LegalUpdate::where('created_from_doc_id', $docId)->first();

        if ($update) {
            $legislation = UpdateCandidateLegislation::where('doc_id', $docId)
                ->whereNotNull('work_id')
                ->pluck('work_id')
                ->all();

            $update->maintainedWorks()->sync($legislation);
        }
    }
}
