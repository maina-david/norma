<?php

namespace App\Observers\Requirements;

use App\Actions\Requirements\Summary\HandleSummarySaving;
use App\Enums\Collaborators\AuditableType;
use App\Enums\Collaborators\AuditActionType;
use App\Models\Requirements\Summary;
use App\Traits\Collaborators\LogsAuditableEvents;

class SummaryObserver
{
    use LogsAuditableEvents;

    protected AuditableType $auditableType = AuditableType::SUMMARY;

    public function saving(Summary $summary): void
    {
        app(HandleSummarySaving::class)->handle($summary);
    }

    /**
     * Handle the Summary "deleted" event.
     *
     * @param \App\Models\Requirements\Summary $summary
     *
     * @return void
     */
    public function deleted(Summary $summary): void
    {
        $this->logAuditEvent($summary->reference_id, AuditActionType::SUMMARY_DELETED);
    }
}
