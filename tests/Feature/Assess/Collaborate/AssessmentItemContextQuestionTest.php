<?php

namespace Tests\Feature\Assess\Collaborate;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemContextQuestion;
use App\Models\Compilation\ContextQuestion;
use Tests\TestCase;

class AssessmentItemContextQuestionTest extends TestCase
{
    /**
     * Get the route that hosts the context question.
     *
     * @param int $assessmentItemId
     *
     * @return string
     */
    protected function getShowRoute(int $assessmentItemId): string
    {
        return route('collaborate.assessment-items.show', ['assessment_item' => $assessmentItemId]);
    }

    /**
     * @return void
     */
    public function testRenderingContextQuestions(): void
    {
        $assessmentItem = AssessmentItem::factory()->create();

        $questions = ContextQuestion::factory()->count(10)->create();

        $assessmentItem->contextQuestions()->sync($questions->map->id->toArray());

        $this->collaboratorSignIn();

        $route = $this->getShowRoute($assessmentItem->id);

        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();

        $questions->each(fn ($question) => $response->assertSee($question->question));
    }

    /**
     * @return void
     */
    public function testAttachingContextQuestions(): void
    {
        $assessmentItem = AssessmentItem::factory()->create();

        $questions = ContextQuestion::factory()->count(10)->create();

        $questions->each(function ($que) use ($assessmentItem) {
            $this->assertDatabaseMissing(AssessmentItemContextQuestion::class, [
                'assessment_item_id' => $assessmentItem->id,
                'context_question_id' => $que->id,
            ]);
        });

        $this->collaboratorSignIn();

        $route = route('collaborate.assessment-item.context-questions.store', ['item' => $assessmentItem->id]);

        $this->validateCollaborateRole($route, 'post');

        $this->followingRedirects()
            ->post($route, ['context_questions' => $questions->map->id->toArray()])
            ->assertSee($questions->first()->predicate);

        $questions->each(function ($que) use ($assessmentItem) {
            $this->assertDatabaseHas(AssessmentItemContextQuestion::class, [
                'assessment_item_id' => $assessmentItem->id,
                'context_question_id' => $que->id,
            ]);
        });
    }

    /**
     * @return void
     */
    public function testDetachingContextQuestions(): void
    {
        $assessmentItem = AssessmentItem::factory()->create();

        $questions = ContextQuestion::factory()->count(2)->create();

        $assessmentItem->contextQuestions()->sync($questions->map->id->toArray());

        $this->collaboratorSignIn();

        $route = route('collaborate.assessment-item.context-questions.destroy', [
            'item' => $assessmentItem->id,
            'question' => $questions->first()->id,
        ]);

        $this->assertDatabaseHas(AssessmentItemContextQuestion::class, [
            'assessment_item_id' => $assessmentItem->id,
            'context_question_id' => $questions->first()->id,
        ]);

        $this->validateCollaborateRole($route, 'delete');

        $this->assertDatabaseMissing(AssessmentItemContextQuestion::class, [
            'assessment_item_id' => $assessmentItem->id,
            'context_question_id' => $questions->first()->id,
        ]);
    }
}
