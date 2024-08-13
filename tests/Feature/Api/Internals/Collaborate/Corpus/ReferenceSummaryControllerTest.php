<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use App\Models\Workflows\Task;
use Tests\TestCase;

class ReferenceSummaryControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testSummaryFlow(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $task = Task::factory()->create();
        $expression = WorkExpression::factory()->create();

        $this->setExpression($expression, $task);

        /** @var Reference $reference */
        $reference = Reference::factory()->create(['work_id' => $expression->work_id]);

        $this->assertNull($reference->summary_id);

        $this->assertDatabaseMissing(Summary::class, ['reference_id' => $reference->id]);

        $this->postJson(route('api.collaborate.references.summary.store', ['reference' => $reference->id]))
            ->assertExactJson([]);

        $this->assertNotNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => null]);

        $this->deleteJson(route('api.collaborate.references.summary.draft.destroy', ['reference' => $reference->id]))
            ->assertExactJson([]);

        $this->assertNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('api.collaborate.references.summary.store', ['reference' => $reference->id]))
            ->assertExactJson([]);

        $this->assertNotNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => null]);

        $this->putJson(route('api.collaborate.references.summary.draft.update', ['reference' => $reference->id]), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);

        $this->followingRedirects()
            ->putJson(route('api.collaborate.references.summary.draft.update', ['reference' => $reference->id]), ['body' => 'Summary here!'])
            ->assertSuccessful();

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);

        $this->getJson(route('api.collaborate.work-expression.references.task.show', ['expression' => $expression->id, 'task' => '-', 'reference' => $reference->id]))
            ->assertSuccessful();

        $this->putJson(route('api.collaborate.references.summary.draft.apply', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        $this->postJson(route('api.collaborate.references.summary.store', ['reference' => $reference->id]))
            ->assertExactJson([]);

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);
        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);

        $this->deleteJson(route('api.collaborate.references.summary.draft.destroy', ['reference' => $reference->id]))
            ->assertExactJson([]);

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        ReferenceSummaryDraft::factory()->for($reference)->create();
        $this->freezeTime(function () use ($reference) {
            $this->deleteJson(route('api.collaborate.references.summary.destroy', ['reference' => $reference->id]))
                ->assertExactJson([]);

            $this->freezeTime(fn () => $this->assertDatabaseMissing(Summary::class, ['reference_id' => $reference->id]));
            $this->freezeTime(fn () => $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]));
        });
    }
}
