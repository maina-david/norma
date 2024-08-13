<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ReferenceChildAndParentsControllerTest extends TestCase
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

        $this->getJson(route('api.collaborate.work-expression.references.task.index', ['expression' => $expression->id, 'task' => '-']))
            ->assertSuccessful()
            ->assertSee($reference->label);

        $this->assertDatabaseMissing(ReferenceReference::class, ['parent_id' => $reference->id, 'child_id' => $child->id]);
        $this->assertDatabaseMissing(ReferenceReference::class, ['parent_id' => $parent->id, 'child_id' => $reference->id]);

        $link = ReferenceLinkType::READ_WITH->value;
        $payload = [
            'references' => [$child->id],
        ];

        $this->postJson(route('api.collaborate.references.children.store', ['reference' => $reference->id, 'type' => $link]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertDatabaseHas(ReferenceReference::class, [
            'parent_id' => $reference->id,
            'child_id' => $child->id,
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $child->work_id,
            'link_type' => $link,
        ]);

        $payload = [
            'references' => [$parent->id],
        ];

        $this->postJson(route('api.collaborate.references.parents.store', ['reference' => $reference->id, 'type' => $link]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertDatabaseHas(ReferenceReference::class, [
            'parent_id' => $parent->id,
            'child_id' => $reference->id,
            'parent_work_id' => $parent->work_id,
            'child_work_id' => $reference->work_id,
            'link_type' => $link,
        ]);

        $this->getJson(route('api.collaborate.work-expression.references.task.show', ['expression' => $expression->id, 'task' => '-', 'reference' => $reference->id]))
            ->assertSuccessful();

        $this->deleteJson(route('api.collaborate.references.parents.destroy', ['reference' => $reference->id, 'related' => $parent->id, 'type' => $link]))
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertDatabaseMissing(ReferenceReference::class, [
            'parent_id' => $parent->id,
            'child_id' => $reference->id,
            'parent_work_id' => $parent->work_id,
            'child_work_id' => $reference->work_id,
            'link_type' => $link,
        ]);

        $this->deleteJson(route('api.collaborate.references.children.destroy', ['reference' => $reference->id, 'related' => $child->id, 'type' => $link]))
            ->assertSuccessful()
            ->assertExactJson([]);

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
