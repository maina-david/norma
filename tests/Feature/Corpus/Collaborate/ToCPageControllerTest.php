<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ToCPageControllerTest extends TestCase
{
    public function testShowingTheTocPage(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();

        $this->get(route('collaborate.toc.index', ['expression' => $expression->id]))
            ->assertSuccessful();

        $this->turboGet(route('collaborate.workflow.tasks.header', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($expression->work->title);
    }
}
