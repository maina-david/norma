<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class IdentifyReferencesPageControllerTest extends TestCase
{
    public function testRenderingPage(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();

        $this->get(route('collaborate.expressions.identify.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('window.activateReference')
            ->assertSee('<div class="h-full w-full" id="app"></div>', false);
    }

    public function testActions(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);

        $this->assertDatabaseHas(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);

        $this->postJson(route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]), [
            'action' => 'delete-references',
            "reference_{$reference->id}" => true,
        ]);

        $this->assertDatabaseMissing(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);

        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $this->assertDatabaseHas(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing(ReferenceContentDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]), [
            'action' => 'request-content-changes',
            "reference_{$reference->id}" => true,
        ]);

        $this->assertDatabaseHas(ReferenceContentDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]), [
            'action' => 'apply-content-drafts',
            "reference_{$reference->id}" => true,
        ]);

        $this->assertDatabaseMissing(ReferenceContentDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]), [
            'action' => 'request-content-changes',
            "reference_{$reference->id}" => true,
        ]);

        $this->assertDatabaseHas(ReferenceContentDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('collaborate.work-expressions.references.actions', ['expression' => $expression->id]), [
            'action' => 'delete-content-changes',
            "reference_{$reference->id}" => true,
        ]);

        $this->assertDatabaseMissing(ReferenceContentDraft::class, ['reference_id' => $reference->id]);
    }
}
