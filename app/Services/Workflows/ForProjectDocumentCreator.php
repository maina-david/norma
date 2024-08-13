<?php

namespace App\Services\Workflows;

use App\Enums\Workflows\DocumentType;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use App\Models\Workflows\Project;

class ForProjectDocumentCreator
{
    /**
     * @param WorkExpression $workExpression
     * @param Project        $project
     * @param int            $locationId
     *
     * @return Document
     */
    public function createCollaborateDocument(
        WorkExpression $workExpression,
        Project $project,
        int $locationId
    ): Document {
        /** @var Document $document */
        $document = Document::create([
            'title' => $workExpression->work->title,
            'document_type' => DocumentType::LEGISLATION->value,
            'work_id' => $workExpression->work_id,
            'work_expression_id' => $workExpression->id,
            'source_id' => $workExpression->work->source_id,
        ]);

        $document->locations()->sync([$locationId]);
        $document->legalDomains()->sync($project->legalDomains);

        return $document;
    }
}
