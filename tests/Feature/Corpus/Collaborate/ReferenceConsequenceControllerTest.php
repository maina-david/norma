<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ReferenceConsequenceControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItDeletesADraft(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id, 'has_consequences_update' => true, 'has_consequences' => false]);

        $this->assertTrue($reference->has_consequences_update);
        $this->assertFalse($reference->has_consequences);

        $this->setExpression($expression);

        $this->turboDelete(route('collaborate.work-expressions.references.consequence.draft.delete', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertFalse($reference->refresh()->has_consequences);
        $this->assertNull($reference->refresh()->has_consequences_update);
    }

    /**
     * @return void
     */
    public function testItDeletesARequirement(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id, 'has_consequences_update' => true, 'has_consequences' => true]);

        $this->assertTrue($reference->has_consequences_update);
        $this->assertTrue($reference->has_consequences);

        $this->setExpression($expression);

        $this->turboDelete(route('collaborate.work-expressions.references.consequence.delete', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertFalse($reference->refresh()->has_consequences);
        $this->assertNull($reference->refresh()->has_consequences_update);
    }
}
