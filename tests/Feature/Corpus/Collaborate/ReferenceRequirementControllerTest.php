<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceRequirementDraft;
use Tests\TestCase;

class ReferenceRequirementControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRequestsADraft(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);

        $this->assertNull($reference->requirementDraft);

        $this->setExpression($expression);

        $this->turboPost(route('collaborate.work-expressions.references.requirement.store', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertNotNull($reference->refresh()->refRequirement);
    }

    /**
     * @return void
     */
    public function testItAppliesADraft(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $reference->refRequirement?->delete();
        ReferenceRequirementDraft::factory()->for($reference)->create();

        $this->assertNotNull($reference->refresh()->requirementDraft);
        $this->assertNull($reference->refresh()->refRequirement);

        $this->setExpression($expression);

        $this->turboPut(route('collaborate.work-expressions.references.requirement.apply', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertNotNull($reference->refresh()->refRequirement);
        $this->assertNull($reference->refresh()->requirementDraft);
    }

    /**
     * @return void
     */
    public function testItDeletesADraft(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $reference->refRequirement?->delete();
        ReferenceRequirementDraft::factory()->for($reference)->create();

        $this->assertNotNull($reference->refresh()->requirementDraft);
        $this->assertNull($reference->refresh()->refRequirement);

        $this->setExpression($expression);

        $this->turboDelete(route('collaborate.work-expressions.references.requirement.draft.delete', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertNull($reference->refresh()->refRequirement);
        $this->assertNull($reference->refresh()->requirementDraft);
    }

    /**
     * @return void
     */
    public function testItDeletesARequirement(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        ReferenceRequirementDraft::factory()->for($reference)->create();

        $this->assertNotNull($reference->requirementDraft);
        $this->assertNotNull($reference->refRequirement);

        $this->setExpression($expression);

        $this->turboDelete(route('collaborate.work-expressions.references.requirement.delete', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertRedirect();

        $this->assertNull($reference->refresh()->refRequirement);
        $this->assertNull($reference->refresh()->requirementDraft);
    }
}
