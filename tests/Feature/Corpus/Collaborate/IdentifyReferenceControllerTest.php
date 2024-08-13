<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Actions\Corpus\Reference\InsertNewReferenceFromTocItem;
use App\Enums\Corpus\ReferenceStatus;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\WorkExpression;
use Mockery\MockInterface;
use Tests\TestCase;

class IdentifyReferenceControllerTest extends TestCase
{
    public function testIndexRendering(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = WorkExpression::factory()->create();

        $this->get(route('collaborate.expressions.identify.old.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('work_expression_content')
            ->assertSee('work_expression_references');
    }

    public function testFetchingReferences(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create();

        $this->setExpression($expression);
        /** @var Reference */
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        /** @var Reference */
        $referenceNotInWork = Reference::factory()->create();

        $this->turboGet(route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText?->plain_text ?? '')
            ->assertDontSee($referenceNotInWork->refPlainText?->plain_text ?? '');
    }

    public function testActivatingAndDeactivatingReferences(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        /** @var Reference */
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);

        $this->turboGet(route('collaborate.work-expressions.identify.references.activate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText?->plain_text ?? '')
            ->assertSee("reference_{$reference->id}_content");

        $this->turboGet(route('collaborate.work-expressions.identify.references.deactivate', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText?->plain_text ?? '')
            ->assertDontSee("reference_{$reference->id}_content");
    }

    public function testContentManagement(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create();
        /** @var Doc */
        $doc = Doc::factory()->create();
        $expression->update(['doc_id' => $doc->id]);
        $this->setExpression($expression);
        $content = $this->faker->paragraph();
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);
        $reference->htmlContent()->save(new ReferenceContent(['reference_id' => $reference->id, 'cached_content' => $content]));

        $this->turboPost(route('collaborate.work-expressions.identify.references.request-update', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->turboGet(route('collaborate.work-expressions.identify.references.content.show', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText?->plain_text)
            ->assertSee("reference_{$reference->id}_content")
            ->assertSee('Save')
            ->assertSee($content);

        $newContent = $this->faker->paragraph;
        $payload = [
            'title' => 'New Title',
            'content' => $newContent,
            'request_requirement' => true,
        ];

        $this->turboPut(route('collaborate.work-expressions.identify.references.content.update', ['expression' => $expression->id, 'reference' => $reference->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame($newContent, $reference->refresh()->contentDraft->html_content);
        $this->assertSame($payload['title'], $reference->refresh()->contentDraft->title);
        $this->assertNotNull($reference->requirementDraft);

        $this->turboGet(route('collaborate.work-expressions.identify.references.content.show', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful()
            ->assertDontSee($content)
            ->assertSee($newContent);

        $this->turboGet(route('collaborate.work-expressions.identify.references.preview-draft', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee($payload['title'])
            ->assertSee($newContent);

        $this->turboPost(route('collaborate.work-expressions.identify.references.apply-drafts', ['expression' => $expression->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->partialMock(UpdateReferencesContentFromDoc::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });

        $this->turboPost(route('collaborate.work-expressions.identify.references.generate-drafts', ['expression' => $expression->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $deletable = Reference::factory()->create(['work_id' => $expression->work_id]);
        $deletable->refRequirement()->delete();

        $this->freezeTime(function () use ($deletable, $reference, $expression) {
            $this->assertDatabaseHas(Reference::class, [
                'id' => $reference->id,
                'deleted_at' => null,
            ]);
            $this->assertDatabaseHas(Reference::class, [
                'id' => $deletable->id,
                'deleted_at' => null,
            ]);

            $this->turboPost(route('collaborate.work-expressions.identify.references.delete-non-requirements', ['expression' => $expression->id]))
                ->assertSessionHasNoErrors()
                ->assertRedirect();

            $this->assertDatabaseHas(Reference::class, [
                'id' => $reference->id,
                'deleted_at' => null,
            ]);
            $this->assertDatabaseMissing(Reference::class, [
                'id' => $deletable->id,
                'deleted_at' => null,
            ]);
        });
    }

    public function testInsertionMovingDeletion(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        /** @var WorkExpression */
        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);
        /** @var Reference */
        $workRef = Reference::where('work_id', $expression->work_id)->first();
        /** @var Reference */
        $reference = Reference::factory()->create(['level' => 1, 'parent_id' => $workRef->id, 'work_id' => $expression->work_id, 'position' => 1]);
        /** @var Reference */
        $reference2 = Reference::factory()->create(['level' => 1, 'parent_id' => $workRef->id, 'work_id' => $expression->work_id, 'position' => 2]);

        $this->assertSame(3, Reference::where('work_id', $expression->work_id)->count());

        $this->followingRedirects()
            ->turboPost(route('collaborate.work-expressions.identify.references.insert-below', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();

        $this->assertSame(4, Reference::where('work_id', $expression->work_id)->count());
        $this->assertSame(1, $reference->refresh()->position);
        $this->assertSame(3, $reference2->refresh()->position);

        $this->followingRedirects()
            ->turboPost(route('collaborate.work-expressions.identify.references.move-up', ['expression' => $expression->id, 'reference' => $reference2->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();
        $this->assertSame(2, $reference2->refresh()->position);

        $this->followingRedirects()
            ->turboPost(route('collaborate.work-expressions.identify.references.move-down', ['expression' => $expression->id, 'reference' => $reference2->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();
        $this->assertSame(3, $reference2->refresh()->position);

        ReferenceContentDraft::create(['reference_id' => $reference->id]);
        $reference->update(['status' => ReferenceStatus::pending()->value]);
        $this->turboDelete(route('collaborate.work-expressions.identify.references.destroy', ['reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('remove');

        $this->assertSame(3, Reference::where('work_id', $expression->work_id)->count());

        $this->assertDatabaseMissing(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);

        $this->partialMock(InsertNewReferenceFromTocItem::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });
        /** @var TocItem */
        $tocItem = TocItem::factory()->create();
        $this->followingRedirects()
            ->turboPost(route('collaborate.work-expressions.identify.references.insert-from-toc', ['expression' => $expression->id, 'tocItem' => $tocItem->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();

        session()->put('collaborate_active_identify_reference', $reference2->id);

        /** @var TocItem */
        $tocItem = TocItem::factory()->create();
        $this->followingRedirects()
            ->turboPost(route('collaborate.work-expressions.identify.references.update-from-toc', ['expression' => $expression->id, 'tocItem' => $tocItem->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful();
    }
}
