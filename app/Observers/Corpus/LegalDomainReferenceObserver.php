<?php

namespace App\Observers\Corpus;

use App\Enums\Collaborators\AuditableType;
use App\Enums\Collaborators\AuditActionType;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Traits\Collaborators\LogsAuditableEvents;

class LegalDomainReferenceObserver
{
    use LogsAuditableEvents;

    protected AuditableType $auditableType = AuditableType::REFERENCE;

    /**
     * Give descendants the relation.
     *
     * @param LegalDomainReference $relation
     *
     * @return void
     */
    public function created(LegalDomainReference $relation): void
    {
        $this->logAuditEvent($relation->reference_id, AuditActionType::LEGAL_DOMAIN_ATTACHED, ['legal_domain_id' => $relation->legal_domain_id]);
    }

    /**
     * Give descendants the relation.
     *
     * @param LegalDomainReference $relation
     *
     * @return void
     */
    public function deleted(LegalDomainReference $relation): void
    {
        $this->logAuditEvent($relation->reference_id, AuditActionType::LEGAL_DOMAIN_DETACHED, ['legal_domain_id' => $relation->legal_domain_id]);
    }
}
