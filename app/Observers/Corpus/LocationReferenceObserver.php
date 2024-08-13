<?php

namespace App\Observers\Corpus;

use App\Enums\Collaborators\AuditableType;
use App\Enums\Collaborators\AuditActionType;
use App\Models\Corpus\Pivots\LocationReference;
use App\Traits\Collaborators\LogsAuditableEvents;

class LocationReferenceObserver
{
    use LogsAuditableEvents;

    protected AuditableType $auditableType = AuditableType::REFERENCE;

    /**
     * Give descendants the relation.
     *
     * @param LocationReference $relation
     *
     * @return void
     */
    public function created(LocationReference $relation): void
    {
        $this->logAuditEvent($relation->reference_id, AuditActionType::LOCATION_ATTACHED, ['location_id' => $relation->location_id]);
    }

    /**
     * Give descendants the relation.
     *
     * @param LocationReference $relation
     *
     * @return void
     */
    public function deleted(LocationReference $relation): void
    {
        $this->logAuditEvent($relation->reference_id, AuditActionType::LOCATION_ATTACHED, ['location_id' => $relation->location_id]);
    }
}
