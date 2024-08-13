<?php

namespace Tests\Feature\Compilation\Collaborate;

use App\Models\Compilation\ContextQuestion;
use App\Models\Ontology\Category;
use Tests\Feature\Traits\HasCollaboratePivotCrudTests;
use Tests\TestCase;

class CategoryContextQuestionControllerTest extends TestCase
{
    use HasCollaboratePivotCrudTests;

    protected static function titleField(): string
    {
        return 'display_label';
    }

    protected static function resourceRoute(): string
    {
        return 'collaborate.compilation.context-questions.categories';
    }

    protected static function resource(): string
    {
        return Category::class;
    }

    protected static function forResource(): ?string
    {
        return ContextQuestion::class;
    }

    protected static function pivotRelationName(): string
    {
        return 'categories';
    }
}
