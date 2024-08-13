<?php

namespace App\Traits\Collaborators;

use App\Enums\Collaborators\AuditActionType;
use App\Models\Collaborators\AuditTrail;
use Exception;
use Illuminate\Support\Facades\Auth;

trait LogsAuditableEvents
{
    /**
     * @param int                  $auditableId
     * @param AuditActionType      $actionType
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function logAuditEvent(int $auditableId, AuditActionType $actionType, array $data = []): void
    {
        if (Auth::user()) {
            if (!isset($this->auditableType)) {
                // @codeCoverageIgnoreStart
                throw new Exception('Please add the $auditableType property to the implenting class');
                // @codeCoverageIgnoreEnd
            }
            AuditTrail::create([
                'auditable_type' => $this->auditableType->value,
                'auditable_id' => $auditableId,
                'user_id' => Auth::id(),
                'action_type' => $actionType->value,
                'data' => json_encode($data),
            ]);
        }
    }
}
