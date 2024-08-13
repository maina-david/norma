<?php

namespace Tests\Feature\Compilation\Collaborate;

use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\RequirementsCollection;
use Tests\Feature\Traits\HasCollaboratePivotCrudTests;
use Tests\TestCase;

class ContextQuestionRequirementsCollectionControllerTest extends TestCase
{
    use HasCollaboratePivotCrudTests;

    protected static function resourceRoute(): string
    {
        return 'collaborate.compilation.context-questions.requirements-collections';
    }

    protected static function resource(): string
    {
        return RequirementsCollection::class;
    }

    protected static function forResource(): ?string
    {
        return ContextQuestion::class;
    }

    protected static function pivotRelationName(): string
    {
        return 'requirementsCollections';
    }
}
