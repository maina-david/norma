<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use Tests\TestCase;

class WorkExpressionPreviewControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testCheckPreview(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        Document::create(['work_id' => $expression->work_id, 'work_expression_id' => $expression->id]);

        $this->get(route('collaborate.work-expressions.preview', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($expression->work->title);
    }
}
