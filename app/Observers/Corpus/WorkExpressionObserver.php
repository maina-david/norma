<?php

namespace App\Observers\Corpus;

use App\Enums\Collaborators\AuditableType;
use App\Enums\Collaborators\AuditActionType;
use App\Jobs\Corpus\CacheWorkContent;
use App\Models\Corpus\WorkExpression;
use App\Traits\Collaborators\LogsAuditableEvents;

class WorkExpressionObserver
{
    use LogsAuditableEvents;

    protected AuditableType $auditableType = AuditableType::WORK_EXPRESSION;

    /**
     * Handle the WorkExpression "created" event.
     *
     * @param \App\Models\Corpus\WorkExpression $workExpression
     *
     * @return void
     */
    public function created(WorkExpression $workExpression)
    {
        $this->logAuditEvent($workExpression->id, AuditActionType::WORK_EXPRESSION_CREATED);
    }

    /**
     * Handle the WorkExpression "updated" event.
     *
     * @param \App\Models\Corpus\WorkExpression $workExpression
     *
     * @return void
     */
    public function updated(WorkExpression $workExpression)
    {
        $this->logAuditEvent($workExpression->id, AuditActionType::WORK_EXPRESSION_UPDATED);

        $current = (bool) $workExpression->show_source_document;
        $previous = (bool) $workExpression->getOriginal('show_source_document');

        if ($workExpression->isDirty('show_source_document') && $current === false && $previous) {
            // @codeCoverageIgnoreStart
            $workExpression->load(['work']);
            CacheWorkContent::dispatch($workExpression->work);
            // @codeCoverageIgnoreEnd
        }
    }
}
