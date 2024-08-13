<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Doc;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class IdentifyWorkExpressionContentControllerTest extends TestCase
{
    public function testShow(): void
    {
        $this->signIn($this->collaborateSuperUser());
        /** @var Doc */
        $doc = Doc::factory()->create();
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create(['doc_id' => $doc->id]);

        $this->turboGet(route('collaborate.work-expressions.identify.show', ['expression' => $expression]))
            ->assertSuccessful()
            ->assertSee($doc->title ?? '');
    }
}
