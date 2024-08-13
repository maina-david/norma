<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ReferenceControllerTest extends TestCase
{
    public function testFetchingReferences(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        Reference::factory()->count(5)->create(['work_id' => $expression->work_id]);
        $first = Reference::where('work_id', $expression->work_id)->first();

        $this->getJson(route('api.collaborate.work-expression.references.task.index', ['expression' => $expression->id, 'task' => '-', 'activate' => $first->id]))
            ->assertSuccessful()
            ->assertSee($first->first()->refPlainText->plain_text);

        $this->getJson(route('api.collaborate.work-expression.references.task.index', ['expression' => $expression->id, 'task' => '-', 'has-requirement' => 1, 'activate' => 123456789]))
            ->assertSuccessful()
            ->assertDontSee($first->first()->refPlainText->plain_text);

        $this->getJson(route('api.collaborate.work-expressions.toc.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($first->first()->refPlainText->plain_text);

        $this->getJson(route('api.collaborate.work-expression.references.task.show', ['expression' => $expression->id, 'task' => '-', 'reference' => $first->id]))
            ->assertSuccessful()
            ->assertSee($first->first()->refPlainText->plain_text);

        $this->getJson(route('api.collaborate.works.index'))
            ->assertSuccessful()
            ->assertJson(['data' => [['id' => $first->work_id]]]);

        $this->getJson(route('api.collaborate.works.references.index', ['work' => $first->work_id]))
            ->assertSuccessful()
            ->assertSee($first->first()->refPlainText->plain_text);
    }
}
