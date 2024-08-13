<?php

namespace App\Http\Controllers\Traits;

use App\Models\Actions\Pivots\ActionAreaReference;
use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceTag;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;

trait UsesMetaDataPivotClasses
{
    /**
     * @param string $relation
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected function getMetaPivotClass(string $relation): string
    {
        return match ($relation) {
            'actionAreas' => ActionAreaReference::class,
            'assessmentItems' => AssessmentItemReference::class,
            'contextQuestions' => ContextQuestionReference::class,
            'categories' => CategoryReference::class,
            'legalDomains' => LegalDomainReference::class,
            'locations' => LocationReference::class,
            // @codeCoverageIgnoreStart
            'tags' => ReferenceTag::class,
            default => LocationReference::class,
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * @param string $relation
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected function getMetaDraftPivotClass(string $relation): string
    {
        return match ($relation) {
            'actionAreas' => ActionAreaReferenceDraft::class,
            'assessmentItems' => AssessmentItemReferenceDraft::class,
            'contextQuestions' => ContextQuestionReferenceDraft::class,
            'categories' => CategoryReferenceDraft::class,
            'legalDomains' => LegalDomainReferenceDraft::class,
            'locations' => LocationReferenceDraft::class,
            // @codeCoverageIgnoreStart
            'tags' => ReferenceTagDraft::class,
            default => LocationReferenceDraft::class,
            // @codeCoverageIgnoreEnd
        };
    }
}
