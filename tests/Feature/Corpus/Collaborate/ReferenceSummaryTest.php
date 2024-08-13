<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use App\Models\Workflows\Task;
use Tests\TestCase;

class ReferenceSummaryTest extends TestCase
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

        $this->post(route('collaborate.corpus.summary.store', ['reference' => $reference->id]))
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id, 'tab' => 'draft']));

        $this->assertNotNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => null]);

        $this->delete(route('collaborate.corpus.summary.draft.destroy', ['reference' => $reference->id]))
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id]));

        $this->assertNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        $this->post(route('collaborate.corpus.summary.store', ['reference' => $reference->id]))
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id, 'tab' => 'draft']));

        $this->assertNotNull($reference->refresh()->summaryDraft);

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => null]);

        $this->put(route('collaborate.corpus.summary.draft.update', ['reference' => $reference->id]), [])
            ->assertSessionHasErrors(['body']);

        $this->followingRedirects()
            ->put(route('collaborate.corpus.summary.draft.update', ['reference' => $reference->id]), ['body' => 'Summary here!'])
            ->assertSessionHasNoErrors()
            ->assertSee('Summary here!');

        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);

        $this->put(route('collaborate.corpus.summary.draft.apply', ['reference' => $reference->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id]));

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        $this->post(route('collaborate.corpus.summary.store', ['reference' => $reference->id]))
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id, 'tab' => 'draft']));

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);
        $this->assertDatabaseHas(ReferenceSummaryDraft::class, ['reference_id' => $reference->id, 'summary_body' => 'Summary here!']);

        $this->delete(route('collaborate.corpus.summary.draft.destroy', ['reference' => $reference->id]))
            ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id]));

        $this->assertDatabaseHas(Summary::class, ['reference_id' => $reference->id]);
        $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]);

        ReferenceSummaryDraft::factory()->for($reference)->create();
        $this->freezeTime(function () use ($reference) {
            $this->delete(route('collaborate.corpus.summary.destroy', ['reference' => $reference->id]))
                ->assertRedirect(route('collaborate.corpus.summary.show', ['reference' => $reference->id]));

            $this->freezeTime(fn () => $this->assertDatabaseMissing(Summary::class, ['reference_id' => $reference->id]));
            $this->freezeTime(fn () => $this->assertDatabaseMissing(ReferenceSummaryDraft::class, ['reference_id' => $reference->id]));
        });
    }
}
