<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ReferenceReferenceRelationControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreatingAndRemovingRelations(): void
    {
        $expression = WorkExpression::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $child = Reference::factory()->create();
        $parent = Reference::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $this->setExpression($expression);

        $this->turboGet(route('collaborate.corpus.reference.for.selector.index', ['work' => $expression->work_id]))
            ->assertSuccessful()
            ->assertSee($reference->label);

        $this->assertDatabaseMissing(ReferenceReference::class, ['parent_id' => $reference->id, 'child_id' => $child->id]);
        $this->assertDatabaseMissing(ReferenceReference::class, ['parent_id' => $parent->id, 'child_id' => $reference->id]);

        $link = ReferenceLinkType::READ_WITH->value;
        $payload = [
            'references' => [$child->id],
            'link_type' => "-{$link}",
        ];

        $this->turboPost(route('collaborate.work-expressions.references.relations.store', ['expression' => $expression->id, 'reference' => $reference->id]), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas(ReferenceReference::class, [
            'parent_id' => $reference->id,
            'child_id' => $child->id,
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $child->work_id,
            'link_type' => $link,
        ]);

        $payload = [
            'references' => [$parent->id],
            'link_type' => "{$link}",
        ];

        $this->turboPost(route('collaborate.work-expressions.references.relations.store', ['expression' => $expression->id, 'reference' => $reference->id]), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas(ReferenceReference::class, [
            'parent_id' => $parent->id,
            'child_id' => $reference->id,
            'parent_work_id' => $parent->work_id,
            'child_work_id' => $reference->work_id,
            'link_type' => $link,
        ]);

        $this->turboDelete(route('collaborate.work-expressions.references.parent.delete', ['expression' => $expression->id, 'reference' => $reference->id, 'related' => $parent->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing(ReferenceReference::class, [
            'parent_id' => $parent->id,
            'child_id' => $reference->id,
            'parent_work_id' => $parent->work_id,
            'child_work_id' => $reference->work_id,
            'link_type' => $link,
        ]);

        $this->turboDelete(route('collaborate.work-expressions.references.child.delete', ['expression' => $expression->id, 'reference' => $reference->id, 'related' => $child->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing(ReferenceReference::class, [
            'parent_id' => $reference->id,
            'child_id' => $child->id,
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $child->work_id,
            'link_type' => $link,
        ]);

        $this->assertDatabaseCount(ReferenceReference::class, 0);
    }
}
