<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\WorkExpression;
use Tests\TestCase;

class ReferenceContentControllerTest extends TestCase
{
    public function testIndexRendering(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();

        $this->get(route('collaborate.expressions.creation.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('work_expression_content')
            ->assertSee('work_expression_references');
    }

    public function testFetchingReferences(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $referenceNotInWork = Reference::factory()->create();

        $this->turboGet(route('collaborate.work-expressions.creation.references.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($reference->title)
            ->assertDontSee($referenceNotInWork->title);
    }

    public function testActivatingAndDeactivatingReferences(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);

        $this->turboGet(route('collaborate.work-expressions.creation.references.activate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->title)
            ->assertSee("reference_{$reference->id}_content");

        $this->turboGet(route('collaborate.work-expressions.creation.references.deactivate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->title)
            ->assertDontSee("reference_{$reference->id}_content");
    }

    public function testContentManagement(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        $content = $this->faker->paragraph();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $reference->htmlContent()->save(new ReferenceContent(['reference_id' => $reference->id, 'cached_content' => $content]));

        $this->turboGet(route('collaborate.work-expressions.creation.references.content.show', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->title)
            ->assertSee("reference_{$reference->id}_content")
            ->assertSee('Save')
            ->assertSee($content);

        $newContent = $this->faker->paragraph;
        $payload = [
            'title' => 'New Title',
            'content' => $newContent,
        ];

        $this->turboPut(route('collaborate.work-expressions.creation.references.content.update', ['expression' => $expression->id, 'reference' => $reference->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame($newContent, $reference->refresh()->htmlContent->cached_content);

        $this->turboGet(route('collaborate.work-expressions.creation.references.content.show', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertDontSee($content)
            ->assertSee($newContent);
    }

    public function testInsertionDeletionAndIndenting(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        $workRef = Reference::where('work_id', $expression->work_id)->first();
        $reference = Reference::factory()->create(['level' => 1, 'parent_id' => $workRef->id, 'work_id' => $expression->work_id, 'start' => 10]);
        $reference2 = Reference::factory()->create(['level' => 1, 'parent_id' => $workRef->id, 'work_id' => $expression->work_id, 'start' => 11]);

        $this->assertSame(1, $reference2->level);
        $this->assertSame($workRef->id, $reference2->parent_id);

        $this->turboPost(route('collaborate.work-expressions.creation.references.indent', ['expression' => $expression->id, 'reference' => $reference2->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();

        $this->assertSame(2, $reference2->refresh()->level);
        $this->assertSame($reference->id, $reference2->parent_id);

        $this->turboPost(route('collaborate.work-expressions.creation.references.outdent', ['expression' => $expression->id, 'reference' => $reference2->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();

        $this->assertSame(1, $reference2->refresh()->level);
        $this->assertSame($workRef->id, $reference2->parent_id);

        $this->assertSame(3, Reference::where('work_id', $expression->work_id)->count());

        $this->turboPost(route('collaborate.work-expressions.creation.references.insert-below', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee("reference_{$reference->id}_after");

        $this->assertSame(4, Reference::where('work_id', $expression->work_id)->count());
        $this->assertSame(10, $reference->refresh()->start);
        $this->assertSame(12, $reference2->refresh()->start);

        $this->turboDelete(route('collaborate.work-expressions.creation.references.destroy', ['reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('remove');

        $this->assertSame(3, Reference::where('work_id', $expression->work_id)->count());

        $this->assertDatabaseMissing(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);
    }
}
