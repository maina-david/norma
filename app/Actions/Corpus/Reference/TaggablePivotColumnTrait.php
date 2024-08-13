<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;

trait TaggablePivotColumnTrait
{
    protected function getPivotColumn(string $pivotClass): string
    {
        return match ($pivotClass) {
            LegalDomainReferenceDraft::class => 'legal_domain_id',
            CategoryReferenceDraft::class => 'category_id',
            LocationReferenceDraft::class => 'location_id',
            ReferenceTagDraft::class => 'tag_id',
            ContextQuestionReferenceDraft::class => 'context_question_id',
            AssessmentItemReferenceDraft::class => 'assessment_item_id',
            ActionAreaReferenceDraft::class => 'action_area_id',
            default => '',
        };
    }
}
