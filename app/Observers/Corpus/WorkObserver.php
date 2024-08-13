<?php

namespace App\Observers\Corpus;

use App\Enums\Collaborators\AuditableType;
use App\Enums\Collaborators\AuditActionType;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Services\Corpus\WorkExpressionContentManager;
use App\Traits\Collaborators\LogsAuditableEvents;
use Throwable;

class WorkObserver
{
    use LogsAuditableEvents;

    protected AuditableType $auditableType = AuditableType::WORK;

    public function created(Work $work): void
    {
        /** @var Reference */
        $ref = Reference::create([
            'level' => 0,
            'start' => -1,
            'volume' => 1,
            'work_id' => $work->id,
            'type' => ReferenceType::work()->value,
            'referenceable_type' => 'works',
            'referenceable_id' => $work->id,
        ]);

        ReferenceText::create([
            'reference_id' => $ref->id,
            'plain_text' => $work->title,
        ]);

        if ($work->primary_location_id) {
            try {
                $ref->locations()->attach($work->primary_location_id);
                // @codeCoverageIgnoreStart
            } catch (Throwable $th) {
            }
            // @codeCoverageIgnoreEnd
        }

        $this->logAuditEvent($work->id, AuditActionType::WORK_CREATED);
    }

    public function updated(Work $work): void
    {
        $dirty = array_diff_key($work->getDirty(), ['uid' => true]);
        $this->logAuditEvent($work->id, AuditActionType::WORK_UPDATED, $dirty);

        if ($work->isDirty('source_id')) {
            $work->load('expressions.convertedDocument');
            $manager = app(WorkExpressionContentManager::class);
            $copy = $work->replicate(['expressions']);
            $copy->unsetRelation('expressions');
            $copy->id = $work->id;
            $copy->source_id = $work->getOriginal('source_id');

            $work->expressions->each(function ($expression) use ($manager, $copy) {
                $expression->setRelation('work', $copy);
                $manager->migrateToDocuments($expression);
            });
        }
    }
}
